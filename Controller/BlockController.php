<?php

namespace CanalTP\MttBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;

use CanalTP\MttBundle\Entity\Block;

class BlockController extends Controller
{
    /**
     * returns form for a given block type or save content of the block using Form factory
     */
    public function editAction($externalNetworkId, $dom_id, $timetableId, $block_type = 'text', $stop_point = null)
    {
        $blockTypeFactory = $this->get('canal_tp_mtt.form.factory.block');
        $blockManager = $this->get('canal_tp_mtt.block_manager');
        $timetableManager = $this->get('canal_tp_mtt.timetable_manager');
        $networkManager = $this->get('canal_tp_mtt.network_manager');
        $network = $networkManager->findOneByExternalId($externalNetworkId);
        $data = array('dom_id' => $dom_id, 'type_id' => $block_type, 'stop_point' => $stop_point);
        

        $block = $blockManager->getBlock($dom_id, $timetableId, $stop_point);

        $blockTypeFactory->init($block_type, $data, $block, $network->getExternalCoverageId());
        $form = $blockTypeFactory->buildForm()
            ->setAction($this->getRequest()->getRequestUri())
            ->setMethod('POST')->getForm();
        $form->handleRequest($this->getRequest());
        $timetable = $timetableManager->getTimetableById($timetableId, $network->getExternalCoverageId());
        if ($form->isValid()) {
            $blockTypeFactory->buildHandler()->process($form->getData(), $timetable);

            return $this->redirect(
                $this->generateUrl(
                    'canal_tp_meth_timetable_edit',
                    array(
                        'externalNetworkId'     => $externalNetworkId,
                        'seasonId'              => $timetable->getLineConfig()->getSeason()->getId(),
                        'externalLineId'        => $timetable->getLineConfig()->getExternalLineId(),
                        'externalRouteId'       => $timetable->getExternalRouteId(),
                        'externalStopPointId'   => $stop_point
                    )
                )
            );
        }

        return $this->render(
            'CanalTPMttBundle:Block:get_form.html.twig',
            array(
                'form'        => $form->createView(),
            )
        );
    }

    public function deleteAction($timetableId, $blockId, $externalNetworkId)
    {
        $networkManager = $this->get('canal_tp_mtt.network_manager');
        $network = $networkManager->findOneByExternalId($externalNetworkId);
        $timetableManager = $this->get('canal_tp_mtt.timetable_manager');
        $repo = $this->getDoctrine()->getRepository('CanalTPMttBundle:Block');

        $block = $repo->find($blockId);
        if (!$block) {
            throw $this->createNotFoundException(
                $this->get('translator')->trans('controller.block.delete.block_not_found', array('%blockid%'=>$blockId), 'exceptions')
            );
        } else {
            $this->getDoctrine()->getEntityManager()->remove($block);
            $this->getDoctrine()->getEntityManager()->flush();
        }
        $timetable = $timetableManager->getTimetableById($timetableId, $network->getExternalCoverageId());

        return $this->redirect(
            $this->generateUrl(
                'canal_tp_meth_timetable_edit',
                array(
                    'externalNetworkId'     => $externalNetworkId,
                    'seasonId'              => $timetable->getLineConfig()->getSeason()->getId(),
                    'externalLineId'        => $timetable->getLineConfig()->getExternalLineId(),
                    'externalRouteId'       => $timetable->getExternalRouteId(),
                    'externalStopPointId'   => null
                )
            )
        );
    }
}
