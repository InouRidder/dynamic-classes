<?php 

// filepath: App\Responders\Responders
trait Responders {
    private $availableResponders = [];
    public function __construct() {
        // Set up the responder class for each method
        $methods = get_class_methods($this);
        foreach($methods as $method) {
            
            $reflect = new ReflectionMethod($this, $method);
            if (preg_match('|__|', $method) || !$reflect->isPublic()) {
                continue;
            }
            
            $class = get_class($this) . $method ."Responder";
            $obj = new $class();
            $this->availableResponders[$method] = $obj;
        }
    }
    
    
    public function __call($method, $args)
    {
        $responder = $this->availableResponders[$this->currentAction()];
        
        if (isset($this->$method) && $this->$method instanceof Closure) {
            return call_user_func_array($this->$method, $args);
        } elseif (method_exists($responder, $method)) {
            return $responder->{$method}($args);
        }
        // In laravel this would be a custom laravel error
        throw new Exception("Method does not exist");
    }
    
    protected function respondWith($args) {
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
// paths and constant names below are incorreect because I didnt make a dir structure yet, but I propose the following file paths / class names as derived from the file path:
// e.g. Responders/UsersController/IndexResponder -> className: IndexResponder;

// filepath: App\Responders\UsersController\IndexResponder
class UserControllerIndexResponder {
    public function fetchUsers() 
    {
        echo('FETCHING ALL THE USERS');
    }

    public function sanitizeUsers($users = []) 
    {
        echo('SANITIZING USERS');
    }
}

// filepath: App\Responders\UsersController\IndexResponder
class UserControllerShowResponder {
    
}

// filepath: App\Responders\UsersController\IndexResponder
class UserControllerCreateResponder {
    public function createUser($params) 
    {
        echo("creating users with $params");
    }

    public function createHashtag($params) 
    {
        echo("creating hashatag with $params");
    }
}

// filepath: App\Responders\UsersController\IndexResponder
class UserControllerUpdateResponder {
    public function updateUser($params) 
    {
        echo("creating users with $params");
    }

    public function updateHashtag($params) 
    {
        echo("creating hashatag with $params");
    }
}

class UserController {
    use Responders;

    public function index() 
    {
        $users = $this->sanitizeUsers($this->fetchUsers());
        $this->respondWith($users);
    }

    public function create() 
    {
        $this->fetchUser();
        $user = $this->createUser($request->validated());
        $this->createHashtag($request->validated());

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

    private function findUser($id) 
    {
        $user = \User::find($id);
        if (!$user) abort(404);
        
        return $user;
    }
}


$controller = new UserController;

$controller->index();

