<?php
// web/index.php


// Misc Notes:
// __DIR__ is /Users/wildcard/Documents/php/project/web

// require  Silex autoload file
require_once __DIR__.'/../vendor/autoload.php';

// require the Twig autoload file
require_once __DIR__.'/../vendor/twig/twig/lib/Twig/Autoloader.php';

// include the Models for users and tweets
include __DIR__.'/../src/models/user.php';
include __DIR__.'/../src/models/tweet.php';

// include the Controller for tweets (which handles user-related controls)
include __DIR__.'/../src/controllers/TweetController.php';

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

// enable method override for HTML forms (e.g. to use PUT and DELETE)
Request::enableHttpMethodParameterOverride();

// create an instance of Silex\Application in a variable, app
$app = new Silex\Application();

// set debug to true for development; set debug to false for production
$app['debug'] = true;


// REGISTER

// register the Service Controller provider
$app->register(new Silex\Provider\ServiceControllerServiceProvider());

// register the Session service provider
$app->register(new Silex\Provider\SessionServiceProvider());

// register the Twig service provider to the app instance

$app->register(new Silex\Provider\TwigServiceProvider(), array(
  // specify the path of the template folder
  'twig.path' => __DIR__.'/../src/views',
));


// CONTROLLER

// Define controller as a service in the application
$app['tweets.controller'] = function() use ($app) {
    return new TwitterClone\TweetController($app);
};


// RESTful ROUTES

// The syntax in the route definition is the name of the service, followed by a single colon (:), followed by the method name. (src: Silex documentation)

// INDEX: show all tweets & show form for new tweet
$app->get('/', "tweets.controller:index");

// SHOW: show a specific tweet
// $app->get('/tweets/{id}', "tweets.controller:show");

// CREATE: add a tweet to the database using user-submitted form information
$app->post('/tweets', "tweets.controller:create");

// NEW: show a form to add a tweet (Note: this form is included on the index page of this app)
// $app->get('/tweets/new', "tweets.controller:new");

// EDIT: show a form to edit an existing tweet
// $app->get('/tweets/edit', "tweets.controller:edit");

// UPDATE: update a tweet in the database using user-sumbitted form information from the edit page
// $app->put('/tweets/{id}', "tweets.controller:update");

// DESTROY: destroy a tweet from the database
$app->delete('/tweets/{id}', "tweets.controller:destroy");



// call the run method on the Silex\Application instance
$app->run();
