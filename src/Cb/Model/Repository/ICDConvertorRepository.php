<?php
namespace Cb\Model\Repository;

use Doctrine\DBAL\DBALException;
use Cb\Db;

class ICDConvertorRepository extends BaseRepository {

    public function datasets()
    {
        $sets =  array(
            'list' => array(
                'title'    => 'Lottery list',
                'desc'     => 'Lottery list',
                'sql'      => 'select kj_id as id, red1, red2, red3 , red4, red5, red6, blue from test.kj',
                'sortby'   => 'kj_id',
                'fields'   => array('red1'=>'','time'=>''),
                'func' => 'test'
            ),
        );
        ksort($sets);
        return $sets;
    }

    public function test($params, $dataset)
    {
putenv('LANG=en_US.UTF-8');

         $last = '';
         if (isset($params['source']) ){
             $source = $params['source'];
             #$last = shell_exec("../src/script/env/bin/python ../src/script/icd_convertor.py --source='".$source."' 2>&1");
             $last = system("../src/script/env/bin/python ../src/script/wiki_convertor.py --dbname carbonado --source='".$source."' 2>&1",$retval);
             #$last = system("../src/script/env/bin/python ../src/script/icd_convertor.py --dbname carbonado --source='".$source."' 2>&1",$retval);
             #$last = exec("../src/script/env/bin/python ../src/script/icd_convertor.py --source='".$source."'", $output);
             #$output = shell_exec("../src/script/env/bin/python ../src/script/icd_convertor.py --source='".$source."' 2>&1");
             #$last = system("../src/script/env/bin/python ../src/script/icd_convertor.py --source='".$source."' 2>&1",$retval);
         }
#        $url='https://en.wikipedia.org/wiki/Acute_liver_failure';
#        $ch=curl_init();
#        $timeout=8;
#        
#        curl_setopt($ch, CURLOPT_URL, $url);
#        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
#        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, $timeout);
#        
#        // Get URL content
#        $content=curl_exec($ch);
#        // close handle to release resources
#        curl_close($ch);
        
        exit;
        #return $output;
    }

    public function utf8_encode_all($dat)
    { 
        if (is_string($dat)) return utf8_encode($dat); 
        if (!is_array($dat)) return $dat; 
        $ret = array(); 
        foreach($dat as $i=>$d) $ret[$i] = $this->utf8_encode_all($d); 
        return $ret; 
    } 
}
?>
