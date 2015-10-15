<?php

namespace Icinga\Module\Director\Tables;

use Icinga\Module\Director\Web\Table\QuickTable;

class DeploymentLogTable extends QuickTable
{
    public function getColumns()
    {
        $columns = array(
            'id'                => 'l.id',
            'peer_identity'     => 'l.peer_identity',
            'start_time'        => 'l.start_time',
            'stage_collected'   => 'l.stage_collected',
            'dump_succeeded'    => 'l.dump_succeeded',
            'startup_succeeded' => 'l.startup_succeeded',
            'checksum'          => 'LOWER(HEX(c.checksum))',
            'duration'          => "l.duration_dump || 'ms'",
        );

        if ($this->connection->getDbType() === 'pgsql') {
            $columns['checksum'] = "LOWER(ENCODE(c.checksum, 'hex'))";
        }

        return $columns;
    }

    protected function getActionUrl($row)
    {
        return $this->url('director/deployment/show', array('id' => $row->id));
    }

    public function getTitles()
    {
        $view = $this->view();
        return array(
            'peer_identity'     => $view->translate('Peer'),
            // 'checksum'          => $view->translate('Checksum'),
            'start_time'          => $view->translate('Time'),
            'dump_succeeded'    => $view->translate('Sent'),
            'startup_succeeded' => $view->translate('Loaded'),
        );
    }

    public function getBaseQuery()
    {
        $db = $this->connection()->getConnection();

        $query = $db->select()->from(
            array('l' => 'director_deployment_log'),
            array()
        )->joinLeft(
            array('c' => 'director_generated_config'),
            'c.checksum = l.config_id', ///Aaaarg
            array()
        )->order('l.start_time DESC');

        return $query;
    }
}