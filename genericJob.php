<?php 


// Disregard, this was some work potentially for the job processing refactor

class IRLParentJob {
    public function handleJob ($className)
    {
        echo("Running $className \n");
    }
}

trait handleJob {
    function handle() { 
        $this->handleJob(__CLASS__);
    }
 };


 $classes = array('LogActivity', 'ClearESInvites');

 foreach($classes as $class) {
     eval("class $class extends IRLParentJob {
         use handleJob;
     }");
    
    if (class_exists($class)) {
        echo("$class generated! \n");
        $instance = new $class;
        $instance->handle();
    }
 }
