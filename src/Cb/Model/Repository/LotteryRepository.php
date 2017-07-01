<?php
namespace Cb\Model\Repository;

use Doctrine\DBAL\DBALException;
use Cb\Db;

class LotteryRepository extends BaseRepository {

    public function datasets()
    {
        $sets =  array(
            'list' => array(
                'title'    => 'Lottery list',
                'desc'     => 'Lottery list',
                'sql'      => 'select kj_id as id, red1, red2, red3 , red4, red5, red6, blue from test.kj',
                'sortby'   => 'kj_id',
                'fields'   => array('red1'=>'','time'=>'')
                //'func' => 'getUserList'
            ),
        );
        ksort($sets);
        return $sets;
    }

}
?>
