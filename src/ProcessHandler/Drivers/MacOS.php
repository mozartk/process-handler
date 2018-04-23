<?php

namespace Craftpip\ProcessHandler\Drivers;

use Craftpip\ProcessHandler\Process as Process2;
use Symfony\Component\Process\Process;

class MacOS implements DriversInterface {

    const defaultOptions = "pid,time,rss,user,sess,args";
    private $options;

    /**
     * @return \Craftpip\ProcessHandler\Process[]
     */
    function getAllProcesses () {
        $this->validate();
        $process = new Process("ps -eo ".$this->options);
        $process->run();
        $op = trim($process->getOutput());

        return $this->parse($op);
    }

    /**
     * @param $pid
     *
     * @return \Craftpip\ProcessHandler\Process[]
     */
    function getProcessByPid ($pid) {
        $this->validate();
        $process = new Process("ps -p $pid -o $this->options");
        $process->run();
        $op = trim($process->getOutput());

        return $this->parse($op);
    }

    /**
     * Set parameters using ps call.
     *
     * @param $options
     *
     */
    public function setOption($options = "pid,time,rss,user,sess,args") {
        $this->options = $options;
    }

    /**
     * get parameters when ps call.
     *
     * @return String
     */
    public function getOption() {
        return $this->options;
    }

    /**
     * Checking options parameter.
     *
     * @return String
     */
    private function validate() {
        $opt = $this->getOption();
        if(trim($opt) === ""){
            $this->setOption(self::defaultOptions);
        }
    }

    /**
     * @param $output
     *
     * @return \Craftpip\ProcessHandler\Process[]
     */
    private function parse ($output) {
        $op = explode("\n", $output);

        $processes = [];
        foreach ($op as $k => $item) {
            if ($k < 1)
                continue;

            $item = explode(" ", preg_replace('!\s+!', ' ', trim($item)));
            $line = [];
            foreach ($item as $i) {
                if ($i != '')
                    $line[] = $i;
            }

            $processName = implode(" ", array_slice($line, 5));
            $processes[] = new Process2($processName, $line[0], false, $line[4], $line[2] . ' KB', 'RUNNING', $line[3], $line[1], false);
        }

        return $processes;
    }
}