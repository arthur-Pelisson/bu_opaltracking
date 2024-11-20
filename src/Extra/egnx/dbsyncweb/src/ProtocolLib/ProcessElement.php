<?php


namespace Egnx\DbSyncWeb\ProtocolLib;


use Egnx\DbSyncWeb\Others\CustomTools;

class ProcessElement
{

    const COMMAND_FILENAME ="command.json";
    const PATTERN_DATAS_FILENAME = '/^(.*)datas#(.*).json/';

    public $folder;
    public $commandfile;
    public $datasfiles=null;

    public function __construct($folder,$files) {

        $this->folder = $folder;
        $array =array();

        foreach($files as $file) {
            if ($file==CustomTools::endsWith($file, self::COMMAND_FILENAME)) {
                $this->commandfile = $file;
            }
            else {
                $matchcount=preg_match_all(self::PATTERN_DATAS_FILENAME, $file, $matches,PREG_PATTERN_ORDER );
                if ($matchcount>0)
                {
                    $array[] = $file;
                }
            }
        }

        sort($array);

        $this->datasfiles = $array;

    }


}