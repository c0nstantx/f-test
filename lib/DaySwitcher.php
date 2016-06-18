<?php

namespace Foodora;
use Foodora\DB\DBInterface;

/**
 * DaySwitcher switches the normal days with special.
 *
 * @author K. Christofilos <kostas.christofilos@gmail.com>
 */
class DaySwitcher
{
    protected $db;

    public function __construct(DBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Switch day from normal to special and vice versa
     *
     * @param \DateTime $date
     * @param null|int $vendorId
     */
    public function switchDay(\DateTime $date, $vendorId = null)
    {
        try {
            $sql = "SELECT * FROM vendor_special_day WHERE special_date = '{$date->format('Y-m-d')}'";
            if (null !== $vendorId) {
                $sql .= " AND vendor_id = $vendorId";
            }
            $specialDays = $this->db->query($sql);
            foreach($specialDays as $specialDay) {
                $this->createSwitch($specialDay);
            }

        } catch (\Exception $ex) {
            echo "Error switching day: {$ex->getMessage()}\n";
        }
    }

    /**
     * Create a new switch for a specific special day
     *
     * @param array $specialDay
     */
    protected function createSwitch(array $specialDay)
    {
        $date = new \DateTime($specialDay['special_date']);
        $normal = $this->getNormalSchedule($specialDay['vendor_id'], $date->format('N'));

        if (!count($normal)) {
            /* If normal schedule doesn't exist, means it was normally closed */
            $newSpecial = array(
                array(
                    'vendor_id' => $specialDay['vendor_id'],
                    'special_date' => $specialDay['special_date'],
                    'event_type' => 'closed',
                    'all_day' => 1,
                    'start_hour' => null,
                    'stop_hour' => null
                )
            );
        } else {
            $newSpecial = array();
            foreach($normal as $norm) {
                $newSpecial[] = array(
                    'vendor_id' => $specialDay['vendor_id'],
                    'special_date' => $specialDay['special_date'],
                    'event_type' => 'opened',
                    'all_day' => $norm['all_day'],
                    'start_hour' => $norm['start_hour'],
                    'stop_hour' => $norm['stop_hour']
                );
                $this->deleteNormal($norm['id']);
            }
        }

        $newNormal = array();
        if ($specialDay['event_type'] === 'opened') {
            $newNormal[] = array(
                'vendor_id' => $specialDay['vendor_id'],
                'weekday' => $date->format('N'),
                'all_day' => $specialDay['all_day'],
                'start_hour' => $specialDay['start_hour'],
                'stop_hour' => $specialDay['stop_hour']
            );
        } else {
            /* Reverse closed hours to find open hours */
            if ($specialDay['all_day'] === '0') {
                $hours = $this->reverseHours($specialDay['start_hour'], $specialDay['stop_hour']);
                foreach($hours as $hour) {
                    $newNormal[] = array(
                        'vendor_id' => $specialDay['vendor_id'],
                        'weekday' => $date->format('N'),
                        'all_day' => $specialDay['all_day'],
                        'start_hour' => $hour['start'],
                        'stop_hour' => $hour['stop']
                    );
                }
            }
        }

        $this->deleteSpecial($specialDay['id']);

        foreach($newNormal as $newNorm) {
            $this->insertNormal($newNorm);
        }

        foreach($newSpecial as $newSpec) {
            $this->insertSpecial($newSpec);
        }
    }

    /**
     * Get normal schedule for a specific vendor and weekday.
     *
     * @param int $vendorId
     * @param int $weekday
     *
     * @return array|null
     */
    protected function getNormalSchedule($vendorId, $weekday)
    {
        try {
            $sql = "SELECT * FROM vendor_schedule WHERE vendor_id = $vendorId AND weekday = $weekday";
            $res = $this->db->query($sql);

            return $res;
        } catch (\Exception $ex) {
            echo "Error getting normal schedule: {$ex->getMessage()}\n";

            return null;
        }
    }

    /**
     * Reverse hour ranges within a day.
     * Returns an array with hour pairs (start, end).
     *
     * @param string $timeStart
     * @param string $timeEnd
     *
     * @return array
     */
    protected function reverseHours($timeStart, $timeEnd)
    {
        $hours = array();
        if ($timeEnd === '23:59:59' && $timeStart !== '00:00:00') {
            $hours[] = array(
                'start' => '00:00:00',
                'stop' => $timeEnd
            );
        } else if ($timeStart === '00:00:00' && $timeEnd !== '23:59:59') {
            $hours[] = array(
                'start' => $timeEnd,
                'stop' => '23:59:59'
            );
        } else if ($timeStart !== '00:00:00' && $timeEnd !== '23:59:59') {
            $hours[] = array(
                'start' => '00:00:00',
                'stop' => $timeStart
            );
            $hours[] = array(
                'start' => $timeEnd,
                'stop' => '23:59:59'
            );
        }

        return $hours;
    }

    /**
     * Delete a normal schedule day record
     *
     * @param int $id
     */
    protected function deleteNormal($id)
    {
        $sql = "DELETE FROM vendor_schedule WHERE id = $id";
        $this->db->query($sql);
    }

    /**
     * Delete a special day record
     *
     * @param int $id
     */
    protected function deleteSpecial($id)
    {
        $sql = "DELETE FROM vendor_special_day WHERE id = $id";
        $this->db->query($sql);
    }

    /**
     * Insert a new normal schedule day record
     *
     * @param array $normal
     */
    protected function insertNormal(array $normal)
    {
        $startHour = $normal['start_hour'] === null ? 'NULL' : "'".$normal['start_hour']."'";
        $stopHour = $normal['stop_hour'] === null ? 'NULL' : "'".$normal['stop_hour']."'";
        $sql = "INSERT INTO vendor_schedule (vendor_id, weekday, all_day, start_hour, stop_hour) 
        VALUE ({$normal['vendor_id']}, {$normal['weekday']}, {$normal['all_day']}, $startHour, $stopHour)";

        $this->db->query($sql);
    }

    /**
     * Insert a new special day record
     *
     * @param array $special
     */
    protected function insertSpecial(array $special)
    {
        $startHour = $special['start_hour'] === null ? 'NULL' : "'".$special['start_hour']."'";
        $stopHour = $special['stop_hour'] === null ? 'NULL' : "'".$special['stop_hour']."'";
        $sql = "INSERT INTO vendor_special_day (vendor_id, special_date, event_type, all_day, start_hour, stop_hour) 
        VALUE ({$special['vendor_id']}, '{$special['special_date']}', '{$special['event_type']}', {$special['all_day']}, $startHour, $stopHour)";

        $this->db->query($sql);
    }
}