<?php 


// spl_autoload_register(function ($unfoundClassName) {
//     {
//         $newClass = new class{}; //create an anonymous class
//         $newClassName = get_class($newClass); //get the name PHP assigns the anonymous class
//         class_alias($newClassName, $unfoundClassName); //alias the anonymous class with your class name
//     }


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
