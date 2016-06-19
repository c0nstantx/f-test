<?php

namespace Foodora\Model;
use Foodora\DB\DBInterface;

/**
 * AbstractDay is the base class for any day process model
 *
 * @author K. Christofilos <kostas.christofilos@gmail.com>
 */
abstract class AbstractDay
{
    /** @var DBInterface */
    protected $db;

    /** @var DBRepository */
    protected $dbRepo;

    public function __construct(DBInterface $db)
    {
        $this->db = $db;
        $this->dbRepo = new DBRepository($db);
    }

    /**
     * Build new normal schedule from special day
     * 
     * @param array $specialDay
     * 
     * @return array
     */
    protected function buildNormalFromSpecial(array $specialDay)
    {
        $date = new \DateTime($specialDay['special_date']);
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
        
        return $newNormal;
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

}