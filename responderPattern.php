<?php 
// The main idea for this pattern is to clean up controller actions and extract logic into their own contained classes which are easy to unit test.
// The controller action should only call well named methods, so that the language is very clear for the user.
// the actions should live in an object called a responder, which responds to a request.

// The main idea is to use the magic method __call to catch any method call to $this and check if the method called perhaps lives in the responder belonging
// to that controller action, if it does, then it calls that method and returns the result.

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

// below are the responder objects that will contain all the methods which execute the necessary statements for controller actions

// Responders ->

// filepath: App\Responders\UsersController\IndexResponder
class UserControllerIndexResponder {
    public function fetchUsers() 
    {
        echo("FETCHING ALL THE USERS \n");
    }

    public function sanitizeUsers($users = []) 
    {
        echo("SANITIZING USERS \n");
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

class BaseController {
    
    // sanity check to see if it works with inheritance
    protected function respondWith($args) {
        return ['response' => $args];
    }
}

class UserController extends BaseController {
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
// logs:
// FETCHING ALL THE USERS
// SANITIZING USERS

