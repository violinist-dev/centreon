<?php
namespace CentreonRemote\Domain\Exporter;

use Psr\Container\ContainerInterface;
use CentreonRemote\Infrastructure\Service\ExporterServiceInterface;
use CentreonRemote\Infrastructure\Export\ExportCommitment;
use CentreonRemote\Domain\Exporter\Traits\ExportPathTrait;
use Centreon\Domain\Repository;

class DowntimeExporter implements ExporterServiceInterface
{

    use ExportPathTrait;

    const EXPORT_FILE_DOWNTIME = 'downtime.yaml';
    const EXPORT_FILE_PERIOD = 'downtime_period.yaml';
    const EXPORT_FILE_CACHE = 'downtime_cache.yaml';
    const EXPORT_FILE_HOST_RELATION = 'downtime_host_relation.yaml';
    const EXPORT_FILE_HOST_GROUP_RELATION = 'downtime_hostgroup_relation.yaml';
    const EXPORT_FILE_SERVICE_RELATION = 'downtime_service_relation.yaml';
    const EXPORT_FILE_SERVICE_GROUP_RELATION = 'downtime_servicegroup_relation.yaml';

    /**
     * @var \Centreon\Infrastructure\Service\CentreonDBManagerService
     */
    private $db;

    /**
     * @var \CentreonRemote\Infrastructure\Export\ExportCommitment
     */
    private $commitment;

    /**
     * Construct
     * 
     * @param \Psr\Container\ContainerInterface $services
     */
    public function __construct(ContainerInterface $services)
    {
        $this->db = $services->get('centreon.db-manager');
    }

    /**
     * Cleanup database
     */
    public function cleanup(): void
    {
        $db = $this->db->getAdapter('configuration_db');

        $db->getRepository(Repository\TimePeriodRepository::class)->truncate();
    }

    /**
     * Export data
     * 
     * @todo add exceptions
     */
    public function export(): void
    {
        // create path
        $this->createPath();

        $pollerId = $this->commitment->getPoller();
        
        $hostTemplateChain = $this->db
            ->getRepository(Repository\HostTemplateRelationRepository::class)
            ->getChainByPoller($pollerId)
        ;
        
        $serviceTemplateChain = $this->db
            ->getRepository(Repository\ServiceRepository::class)
            ->getChainByPoller($pollerId)
        ;

        // Extract data
        $downtimes = $this->db
            ->getRepository(Repository\DowntimeRepository::class)
            ->export($pollerId, $hostTemplateChain, $serviceTemplateChain)
        ;

        $downtimePeriods = $this->db
            ->getRepository(Repository\DowntimePeriodRepository::class)
            ->export($pollerId, $hostTemplateChain, $serviceTemplateChain)
        ;

        $downtimeCaches = $this->db
            ->getRepository(Repository\DowntimeCacheRepository::class)
            ->export($pollerId, $hostTemplateChain)
        ;

        $downtimeHostRelation = $this->db
            ->getRepository(Repository\DowntimeHostRelationRepository::class)
            ->export($pollerId, $hostTemplateChain)
        ;

        $downtimeHostGroupRelation = $this->db
            ->getRepository(Repository\DowntimeHostGroupRelationRepository::class)
            ->export($pollerId, $hostTemplateChain)
        ;

        $downtimeServiceRelation = $this->db
            ->getRepository(Repository\DowntimeServiceRelationRepository::class)
            ->export($pollerId, $hostTemplateChain)
        ;

        $downtimeServiceGroupRelation = $this->db
            ->getRepository(Repository\DowntimeServiceGroupRelationRepository::class)
            ->export($pollerId, $serviceTemplateChain)
        ;

        $this->commitment->getParser()::dump($downtimes, $this->getFile(static::EXPORT_FILE_DOWNTIME));
        $this->commitment->getParser()::dump($downtimePeriods, $this->getFile(static::EXPORT_FILE_PERIOD));
        $this->commitment->getParser()::dump($downtimeCaches, $this->getFile(static::EXPORT_FILE_CACHE));
        $this->commitment->getParser()::dump($downtimeHostRelation, $this->getFile(static::EXPORT_FILE_HOST_RELATION));
        $this->commitment->getParser()::dump($downtimeHostGroupRelation, $this->getFile(static::EXPORT_FILE_HOST_GROUP_RELATION));
        $this->commitment->getParser()::dump($downtimeServiceRelation, $this->getFile(static::EXPORT_FILE_SERVICE_RELATION));
        $this->commitment->getParser()::dump($downtimeServiceGroupRelation, $this->getFile(static::EXPORT_FILE_SERVICE_GROUP_RELATION));
    }

    /**
     * Import data
     * 
     * @todo add exceptions
     */
    public function import(): void
    {
        // skip if no data
        if (!is_dir($this->getPath())) {
            return;
        }

        $db = $this->db->getAdapter('configuration_db');

        // start transaction
        $db->beginTransaction();

        // allow insert records without foreign key checks
        $db->query('SET FOREIGN_KEY_CHECKS=0;');

        // truncate tables
        $this->cleanup();

        // insert downtimes
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_DOWNTIME);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('downtime', $data);
            }
        })();

        // insert downtime periods
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_PERIOD);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('downtime_period', $data);
            }
        })();

        // insert downtime cache
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_CACHE);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('downtime_cache', $data);
            }
        })();

        // insert downtime host relation
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_HOST_RELATION);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('downtime_host_relation', $data);
            }
        })();

        // insert downtime host group relation
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_HOST_GROUP_RELATION);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('downtime_hostgroup_relation', $data);
            }
        })();

        // insert downtime service relation
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_SERVICE_RELATION);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('downtime_service_relation', $data);
            }
        })();

        // insert downtime service group relation
        (function() use ($db) {
            $exportPathFile = $this->getFile(static::EXPORT_FILE_SERVICE_GROUP_RELATION);
            $result = $this->commitment->getParser()::parse($exportPathFile);

            foreach ($result as $data) {
                $db->insert('downtime_servicegroup_relation', $data);
            }
        })();

        // restore foreign key checks
        $db->query('SET FOREIGN_KEY_CHECKS=1;');

        // commit transaction
        $db->commit();
    }

    public function setCommitment(ExportCommitment $commitment): void
    {
        $this->commitment = $commitment;
    }

    public static function getName(): string
    {
        return 'downtime';
    }
}
