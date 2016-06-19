<?php

namespace Foodora\Model;
use Foodora\DB\DBInterface;

/**
 * DBRepository is a repo for ready to go queries
 *
 * @author K. Christofilos <kostas.christofilos@gmail.com>
 */
class DBRepository
{
    /** @var DBInterface */
    protected $db;
    
    public function __construct(DBInterface $db)
    {
        $this->db = $db;
    }

    /**
     * Get list of backup days
     * 
     * @param \DateTime $date
     * @param int       $vendorId
     * 
     * @return array
     */
    public function getBackupDays(\DateTime $date, $vendorId = null)
    {
        $sql = "SELECT * FROM `temp_fix_days` WHERE weekday = {$date->format('N')}";
        if (null !== $vendorId) {
            $sql .= " AND vendor_id = $vendorId";
        }
        
        return $this->db->query($sql);
    }
    
    /**
     * Create the temporary table
     */
    public function createTempTable()
    {
        $sql = "CREATE TABLE IF NOT EXISTS `temp_fix_days` (
            `id` bigint(20) NOT NULL AUTO_INCREMENT,
            `vendor_id` bigint(20) NOT NULL,
            `weekday` tinyint(4) NOT NULL,
            `all_day` tinyint(1) NOT NULL,
            `start_hour` time DEFAULT NULL,
            `stop_hour` time DEFAULT NULL,
            PRIMARY KEY (`id`),
            KEY `fk_vendor` (`vendor_id`),
            CONSTRAINT `fk_temp_vendor` FOREIGN KEY (`vendor_id`) REFERENCES `vendor` (`id`)
            ) ENGINE=InnoDB AUTO_INCREMENT=0 DEFAULT CHARSET=utf8;
        ";

        $this->db->query($sql);
    }

    /**
     * Get special day schedule
     * 
     * @param \DateTime $date
     * @param int       $vendorId
     * 
     * @return array
     */
    public function getSpecialDays(\DateTime $date, $vendorId = null)
    {
        $sql = "SELECT * FROM vendor_special_day WHERE special_date = '{$date->format('Y-m-d')}'";
        if (null !== $vendorId) {
            $sql .= " AND vendor_id = $vendorId";
        }
        
        return $this->db->query($sql);
        
    }
    
    /**
     * Get normal schedule for a specific vendor and weekday.
     *
     * @param int $vendorId
     * @param int $weekday
     *
     * @return array|null
     */
    public function getNormalSchedule($vendorId, $weekday)
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
     * Delete a normal schedule day record
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteNormal($id)
    {
        $sql = "DELETE FROM vendor_schedule WHERE id = $id";
        return $this->db->query($sql);
    }

    /**
     * Delete a special day record
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteSpecial($id)
    {
        $sql = "DELETE FROM vendor_special_day WHERE id = $id";
        return $this->db->query($sql);
    }

    /**
     * Delete a backup day record
     *
     * @param int $id
     *
     * @return bool
     */
    public function deleteBackup($id)
    {
        $sql = "DELETE FROM temp_fix_days WHERE id = $id";
        return $this->db->query($sql);
    }

    /**
     * Insert a backup day record
     *
     * @param array $backup
     *
     * @return bool
     */
    public function insertBackup(array $backup)
    {
        $startHour = $backup['start_hour'] === null ? 'NULL' : "'".$backup['start_hour']."'";
        $stopHour = $backup['stop_hour'] === null ? 'NULL' : "'".$backup['stop_hour']."'";
        $sql = "INSERT INTO `temp_fix_days` (vendor_id, weekday, all_day, start_hour, stop_hour) 
        VALUE ({$backup['vendor_id']}, {$backup['weekday']}, {$backup['all_day']}, $startHour, $stopHour);";

        return $this->db->query($sql);
    }

    /**
     * Insert a new normal schedule day record
     *
     * @param array $normal
     *
     * @return bool
     */
    public function insertNormal(array $normal)
    {
        $startHour = $normal['start_hour'] === null ? 'NULL' : "'".$normal['start_hour']."'";
        $stopHour = $normal['stop_hour'] === null ? 'NULL' : "'".$normal['stop_hour']."'";
        $sql = "INSERT INTO `vendor_schedule` (vendor_id, weekday, all_day, start_hour, stop_hour) 
        VALUE ({$normal['vendor_id']}, {$normal['weekday']}, {$normal['all_day']}, $startHour, $stopHour);";

        return $this->db->query($sql);
    }

    /**
     * Insert a new special day record
     *
     * @param array $special
     *
     * @return bool
     */
    public function insertSpecial(array $special)
    {
        $startHour = $special['start_hour'] === null ? 'NULL' : "'".$special['start_hour']."'";
        $stopHour = $special['stop_hour'] === null ? 'NULL' : "'".$special['stop_hour']."'";
        $sql = "INSERT INTO `vendor_special_day` (vendor_id, special_date, event_type, all_day, start_hour, stop_hour) 
        VALUE ({$special['vendor_id']}, '{$special['special_date']}', '{$special['event_type']}', {$special['all_day']}, $startHour, $stopHour);";

        return $this->db->query($sql);
    }
}