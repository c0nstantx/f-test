<?php

namespace Foodora\Model;

/**
 * DaySwitcher switches the normal days with special.
 *
 * @author K. Christofilos <kostas.christofilos@gmail.com>
 */
class DaySwitcher extends AbstractDay
{

    /**
     * Switch day from normal to special and vice versa
     *
     * @param \DateTime $date
     * @param null|int $vendorId
     */
    public function switchDay(\DateTime $date, $vendorId = null)
    {
        try {
            $specialDays = $this->dbRepo->getSpecialDays($date, $vendorId);
            foreach($specialDays as $specialDay) {
                $this->createSwitch($specialDay);
            }

        } catch (\Exception $ex) {
            echo "Error switching day: {$ex->getMessage()}\n";
        }
    }

    /**
     * Create a new switch for a specific special day.
     *
     * @param array $specialDay
     */
    protected function createSwitch(array $specialDay)
    {
        $date = new \DateTime($specialDay['special_date']);
        $normalDays = $this->dbRepo->getNormalSchedule($specialDay['vendor_id'], $date->format('N'));

        /**
         * Create new special day from current normal schedule
         */
        $newSpecial = array();
        if (!count($normalDays)) {
            /* If normal schedule doesn't exist, it means it was normally closed */
            $newSpecial[] = array(
                'vendor_id' => $specialDay['vendor_id'],
                'special_date' => $specialDay['special_date'],
                'event_type' => 'closed',
                'all_day' => 1,
                'start_hour' => null,
                'stop_hour' => null
            );
        } else {
            foreach($normalDays as $normalDay) {
                $newSpecial[] = array(
                    'vendor_id' => $specialDay['vendor_id'],
                    'special_date' => $specialDay['special_date'],
                    'event_type' => 'opened',
                    'all_day' => $normalDay['all_day'],
                    'start_hour' => $normalDay['start_hour'],
                    'stop_hour' => $normalDay['stop_hour']
                );
                $this->dbRepo->deleteNormal($normalDay['id']);
            }
        }

        $newNormal = $this->buildNormalFromSpecial($specialDay);
        $this->dbRepo->deleteSpecial($specialDay['id']);

        /**
         * Insert new records to database
         */
        foreach($newNormal as $newNorm) {
            $this->dbRepo->insertNormal($newNorm);
        }

        foreach($newSpecial as $newSpec) {
            $this->dbRepo->insertSpecial($newSpec);
        }
    }
}