<?php

namespace Foodora\Model;

/**
 * DayFixer will assign special day schedule to normal one and backup the current
 * normal schedule
 *
 * @author K. Christofilos <kostas.christofilos@gmail.com>
 */
class DayFixer extends AbstractDay
{
    /**
     * Fix a specific day and vendor
     *
     * @param \DateTime $date
     * @param int $vendorId
     */
    public function fixDay(\DateTime $date, $vendorId = null)
    {
        try {
            $specialDays = $this->dbRepo->getSpecialDays($date, $vendorId);
            $specialVendor = $specialDays[0]['vendor_id'];
            $normalDays = $this->dbRepo->getNormalSchedule($specialVendor, $date->format('N'));
            /* If shop was normally closed */
            if (empty($normalDays)) {
                $normalDays = [
                    [
                        'vendor_id' => $specialVendor,
                        'weekday' => $date->format('N'),
                        'all_day' => true,
                        'start_hour' => null,
                        'stop_hour' => null,
                    ]
                ];
            }
            $saveBackup = true;
            foreach($specialDays as $specialDay) {
                if ($this->isAlreadyFixed($specialDay)) {
                    $saveBackup = false;
                    echo "Weekday '{$date->format('N')}' for vendor {$specialDay['vendor_id']} is already fixed\n";
                } else {
                    $this->replaceDay($specialDay, $normalDays);
                }
            }
            if ($saveBackup) {
                $this->backupDay($normalDays);
            }

        } catch (\Exception $ex) {
            echo "Error fixing day: {$ex->getMessage()}\n{$ex->getTraceAsString()}\n";
        }
    }

    /**
     * Restore specific day's schedule from backup
     * 
     * @param \DateTime $date
     * @param int       $vendorId
     */
    public function restoreDay(\DateTime $date, $vendorId = null)
    {
        try {
            $backupDays = $this->dbRepo->getBackupDays($date, $vendorId);
            $newNormalDays = array();
            foreach($backupDays as $backupDay) {
                /**
                 * Delete current normal schedule
                 */
                $normalDays = $this->dbRepo->getNormalSchedule($backupDay['vendor_id'], $backupDay['weekday']);
                foreach($normalDays as $normalDay) {
                    $this->dbRepo->deleteNormal($normalDay['id']);
                }

                /**
                 * Build normal schedule from backup
                 */
                $this->dbRepo->deleteBackup($backupDay['id']);

                if ($backupDay['all_day'] && $backupDay['start_hour'] === null && $backupDay['stop_hour'] === null) {
                    continue;
                }
                $newNormalDays[] = array(
                    'vendor_id' => $backupDay['vendor_id'],
                    'weekday' => $backupDay['weekday'],
                    'all_day' => $backupDay['all_day'],
                    'start_hour' => $backupDay['start_hour'],
                    'stop_hour' => $backupDay['stop_hour']
                );
            }

            /**
             * Restore backup
             */
            foreach($newNormalDays as $newNormalDay) {
                $this->dbRepo->insertNormal($newNormalDay);
            }

        } catch (\Exception $ex) {
            echo "Error restoring day: {$ex->getMessage()}\n{$ex->getTraceAsString()}\n";
        }
    }

    /**
     * Replace normal schedule with new schedule from special day
     *
     * @param array $specialDay
     */
    protected function replaceDay(array $specialDay, array $normalDays)
    {
        /**
         * Delete current normal schedule
         */
        foreach($normalDays as $normalDay) {
            if (isset($normalDay['id'])) {
                $this->dbRepo->deleteNormal($normalDay['id']);
            }
        }

        /**
         * Insert new records to database
         */
        $newNormal = $this->buildNormalFromSpecial($specialDay);
        foreach($newNormal as $newNorm) {
            $this->dbRepo->insertNormal($newNorm);
        }

    }

    /**
     * Backup normal schedule days based on special day
     *
     * @param array $specialDays
     */
    protected function backupDay(array $normalDays)
    {
        if (!$this->tempTableExists()) {
            $this->dbRepo->createTempTable();
        }

        foreach($normalDays as $normalDay) {
            $backupDay = array(
                'vendor_id' => $normalDay['vendor_id'],
                'weekday' => $normalDay['weekday'],
                'all_day' => $normalDay['all_day'],
                'start_hour' => $normalDay['start_hour'],
                'stop_hour' => $normalDay['stop_hour'],
            );
            $this->dbRepo->insertBackup($backupDay);
        }

    }

    /**
     * Returns if the special day is already fixed
     *
     * @param array $specialDay
     *
     * @return bool
     */
    protected function isAlreadyFixed(array $specialDay)
    {
        if (!$this->tempTableExists()) {
            return false;
        }

        $date = new \DateTime($specialDay['special_date']);
        $sql = "SELECT * FROM `temp_fix_days` 
        WHERE vendor_id = {$specialDay['vendor_id']} 
        AND weekday = '{$date->format('N')}'";

        $result = $this->db->query($sql);

        return !empty($result);
    }

    /**
     * Returns if temp table exists
     *
     * @return bool
     */
    protected function tempTableExists()
    {
        try {
            $sql = 'SELECT * FROM `temp_fix_days`';
            $result = $this->db->query($sql);

            return true;
        } catch (\Exception $ex) {
            return false;
        }
    }
}