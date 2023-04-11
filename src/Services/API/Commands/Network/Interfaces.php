<?php 

namespace RouterboardOS\Services\API\Commands\Network;

use RouterboardOS\Services\API\Bridge;

class Interfaces 
{
    public function __construct (
        Bridge $bridge,
        protected string $sentence = '/interface/print'
    ) {
        $this->bridge = $bridge;
    }

    public function data(): array 
    {
        $this->bridge->write_sentence([$this->sentence]);

        $response = $this->read_sentence();
    
        $interfaces = [];
        $current = [];
    
        foreach ($response as $word) 
        {
            if ($word === '!re') 
            {
                if (!empty($current)) 
                {
                    $interfaces[] = $current;
                }

                $current = [];
            } 
            else 
            {
                $kv = explode('=', $word, 2);

                if (count($kv) === 2) 
                {
                    $current[$kv[0]] = $kv[1];
                }
            }
        }
    
        if (!empty($current)) 
        {
            $interfaces[] = $current;
        }
    
        return $interfaces;
    }

}