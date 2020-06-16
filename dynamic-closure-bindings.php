<?php 

trait UserResponders {

}

class BaseResponder {
    public function bind() {
        return $this;
    }
}

class UserControllerIndexResponder extends BaseResponder {
    public function fetchUsers() 
    {
        echo('FETCHING ALL THE USERS');
    }

    public function sanitizeUsers($users = []) 
    {
        echo('SANITIZING USERS');
    }
}

class UserControllerCreateResponder extends BaseResponder {
    public function fetchUsers() 
    {
        echo('FETCHING ALL THE USERS');
    }

    public function sanitizeUsers($users = []) 
    {
        echo('SANITIZING USERS');
    }
}

class UserControllerUpdateResponder extends BaseResponder {
    public function fetchUsers() 
    {
        echo('FETCHING ALL THE USERS');
    }

    public function sanitizeUsers($users = []) 
    {
        echo('SANITIZING USERS');
    }
}

class UserControllerShowResponder extends BaseResponder {
    public function fetchUsers() 
    {
        echo('FETCHING ALL THE USERS');
    }

    public function sanitizeUsers($users = []) 
    {
        echo('SANITIZING USERS');
    }
}

class BaseController {
    private $available_methods = [];
    public function __construct() {
        $methods = get_class_methods($this);
        foreach($methods as $method) {
            
            $reflect = new ReflectionMethod($this, $method);
            if (preg_match('|__|', $method) || !$reflect->isPublic()) {
                continue;
            }
            
            $class = get_class($this) . $method ."Responder";
            $obj = new $class();
            $this->currentResponderClass = $obj;
            $methods = get_class_methods(get_class($obj));
            $this->available_methods[$method] = $methods;
        }
    } 

    public function __call($method, $args)
    {
        if (isset($this->$method) && $this->$method instanceof Closure) {
            return call_user_func_array($this->$method, $args);
        } elseif ($this->definedMethodsFor($method)) {
            return $this->currentResponderClass->{$method}();
        }
        // In laravel this would be a custom laravel error
        throw new Exception("Method does not exist");
    }

    private function definedMethodsFor($method) 
    {
        return in_array(
            $method, 
            $this->available_methods[$this->currentAction()]
        );
    }

    private function respondWith($args) {
        return ['response' => $args];
    }

    private function currentAction() 
    {
        // to test
        return 'index';
        // in laravel
        return explode('@', Route::currentRouteAction())[1];
    }
}

class UserController extends BaseController {
    public function index() 
    {
        $users = $this->sanitizeUsers($this->fetchUsers());
        // $this->respondWith($users);
    }

    public function create() 
    {
        $this->fetchUser();
        // $user = $this->createUser($request->validated());
        // $this->createHashtag($request->validated());

        $this->respondWith($user);
    }

    public function show($id) 
    {
        $user = $this->findUser($id);
        $this->respondWith($user);
    }

    public function update()
    {
        $user = $this->findUser($id);
        $this->updateUser($user, $request->validated());
        $this->updateHashtag($request->validated());
        $this->respondWith($user);
    }

    // PRIVATE FUNCTIONS

    private function findUser($id) 
    {
        $user = \User::find($id);
        if (!$user) abort(404);
        
        return $user;
    }
}


$controller = new UserController;

$controller->index();

