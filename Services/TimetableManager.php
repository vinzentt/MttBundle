<?php
/**
 * Description of TimetableManager
 *
 * @author vdegroote
 */
namespace CanalTP\MttBundle\Services;

use Doctrine\Common\Persistence\ObjectManager;
use CanalTP\MttBundle\Entity\Timetable;
use CanalTP\MttBundle\Services\Navitia;

class TimetableManager
{
    private $timetable = null;
    private $line = null;
    private $navitia = null;
    private $repository = null;
    private $om = null;
    private $calendars = null;

    public function __construct(ObjectManager $om, Navitia $navitia, LineManager $lineManager)
    {
        $this->timetable = null;
        $this->om = $om;
        $this->navitia = $navitia;
        $this->lineManager = $lineManager;
        $this->repository = $this->om->getRepository('CanalTPMttBundle:Timetable');
    }

    /*
     * @function find twig path for parent line
     */
    private function initAdditionalData($externalRouteId, $externalCoverageId)
    {
        $data = $this->navitia->getRouteData($externalRouteId, $externalCoverageId);
        $line = $this->lineManager->getLineConfigByExternalLineId($data->line->id);

        $this->timetable->setLine($line);
        $this->timetable->setTitle($data->name);
        $this->calendars = $this->navitia->getRouteCalendars($externalCoverageId, $externalRouteId);
    }

    /*
     * get corresponding blocks and index them by dom_id
     */
    private function initBlocks()
    {
        $timetableBlocks = $this->repository->getBlocks($this->timetable);

        if (count($timetableBlocks) > 0) {
            $blocks = array();

            foreach ($timetableBlocks as $block) {
                $blocks[$block->getDomId()] = $block;
            }
            if (count($blocks) > 0) {
                $this->timetable->setBlocks($blocks);
            }
        }
    }

    /**
     * Return timetable Object with navitia data added
     *
     * @param  Integer   $id PK in bdd
     * @return timetable
     */
    public function getTimetableById($timetableId, $externalCoverageId)
    {
        $this->timetable = $this->repository->find($timetableId);
        $this->initAdditionalData($this->timetable->getExternalRouteId(), $externalCoverageId);
        $this->initBlocks();

        return $this->timetable;
    }

    /**
     * Return timetable Object with navitia data added
     *
     * @param  Integer   $externalId
     * @return timetable
     */
    public function getTimetable($externalRouteId, $externalCoverageId, $lineConfig)
    {
        $this->timetable = $this->repository->getTimetableByRouteExternalId($externalRouteId, $lineConfig);
        $this->initAdditionalData($externalRouteId, $externalCoverageId);
        $this->initBlocks();

        return $this->timetable;
    }


    /**
     * Return timetable Object with navitia data added
     *
     * @param  Integer   $externalId
     * @return timetable
     */
    public function findTimetableByExternalRouteIdAndLineConfig($externalRouteId, $lineConfig)
    {
        $this->timetable = $this->repository->getTimetableByRouteExternalId($externalRouteId, $lineConfig);

        return $this->timetable;
    }


    /**
     * Return timetable Object
     *
     * @param  Integer   $externalId
     * @return timetable
     */
    public function find($id)
    {
        return $this->repository->find($id);
    }
    
    /**
     * Return timetable 
     *
     * @param  Object $timetable
     * @param  Object $destLineConfig
     * @return timetable
     */
    public function copy($timetable, $destLineConfig)
    {
        $timetableCloned = clone $timetable;
        $timetableCloned->setLineConfig($destLineConfig);
        
        return $timetableCloned;
    }
}
