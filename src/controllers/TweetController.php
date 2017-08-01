<?php
namespace TwitterClone;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Silex\Application;
use Silex\Api\ControllerProviderInterface;

class TweetController // implements ControllerProviderInterface
{

  public function __construct () {
    $config = new \Doctrine\DBAL\Configuration();
    $connectionParams = array(
        'driver'    => 'pdo_mysql',
        'host'      => 'localhost',
        'dbname'    => 'php_buzz_db',
        'user'      => 'root',
        'password'  => 'root',
        'charset'   => 'utf8',
    );
    $this->conn = \Doctrine\DBAL\DriverManager::getConnection($connectionParams, $config);
  }

  public function check_user ($user) {
    // using Doctrine QueryBuilder, check if a name exists in the users table; note: this is case insensitive
    $qb = $this->conn->createQueryBuilder();
    $qb
      ->select('id')
      ->from('user')
      ->where('name = ?')
      ->setParameter(0, $user);
    $user = $qb->execute()->fetch();
    // if record is found, returns the user object; if record is not found, returns false
    return $user;
  }

  // GET route for the home page
  public function index (Application $app) {
    // using Doctrine QueryBuilder, select all tweets to be displayed on home page; join with user table to get author names
    $qb = $this->conn->createQueryBuilder();
    $qb
      ->select('t.id', 'u.name', 't.content', 't.created_at')
      ->from('tweet', 't')
      ->innerJoin('t', 'user', 'u', 'u.id = t.author_id')
      ->orderBy('t.created_at', 'DESC');
    $tweets = $qb->execute()->fetchAll();
    // render the index page using twig; send the tweets to be populated on the HTML page
    return $app['twig']->render('index.html', array('tweets' => $tweets));
  }

  // POST route for creating a new tweet
  public function create (Application $app, Request $request) {
    // store information from the user-submitted form in variables
    $author = $request->get('author');
    $content = $request->get('content');
    // set a variable to the current time
    $time = new \DateTime();
    $time_str = $time->format('Y-m-d H:i:s');
    // set a boolean to indicate if the form input is valid
    $validation = true;

    // validate the name input; store error messages in session as Flash messages to be rendered on the template
    if (strlen($author) < 1) {
      $app['session']->getFlashBag()->add('errors', "Name cannot be blank.");
      $validation = false;
    } else if (strlen($author) > 32) {
      $app['session']->getFlashBag()->add('errors', "Name is too long.");
      $validation = false;
    }
    // validate the tweet input
    if (strlen($content) < 1) {
      $app['session']->getFlashBag()->add('errors', "Tweet cannot be blank.");
      $validation = false;
    } else if (strlen($content) > 140) {
      $app['session']->getFlashBag()->add('errors', "Tweet is too long.");
      $validation = false;
    }

    // if the input data passes validation, add the information to the appropriate tables
    if ($validation) {
      // use the class method 'check_user' to check if the name already exists in the user table
      $user = $this->check_user($author);
      // if the name does not already exist in the user table, add a new user
      if ($user == false) {
        try {
          // using Doctrine QueryBuilder, add the new user to the users table
          $qb = $this->conn->createQueryBuilder();
          $qb
            ->insert('user')
            ->setValue('name', '?')
            ->setValue('created_at', '?')
            ->setValue('updated_at', '?')
            ->setParameter(0, $author)
            ->setParameter(1, $time_str)
            ->setParameter(2, $time_str);
          $qb->execute();
          // use the class method 'check_user' to get the user object for the new user
          $user = $this->check_user($author);
          // catch exceptions (these should not be hit because of the validations earlier in this method)
        } catch (\PDOException $e) {
          $app['session']->getFlashBag()->add('errors', "Name is not valid.");
          // $app['session']->getFlashBag()->add('errors', $e->getMessage());
        } catch (\UniqueConstraintViolationException $e) {
          $app['session']->getFlashBag()->add('errors', "Name is not valid.");
        } catch (\Exception $e) {
          $app['session']->getFlashBag()->add('errors', "Name is not valid.");
        }
      }

      try {
        // using Doctrine QueryBuilder, add the new tweet to the tweets table
        $qb = $this->conn->createQueryBuilder();
        $qb
          ->insert('tweet')
          ->setValue('content', '?')
          ->setValue('author_id', '?')
          ->setValue('created_at', '?')
          ->setValue('updated_at', '?')
          ->setParameter(0, $content)
          ->setParameter(1, $user['id'])
          ->setParameter(2, $time_str)
          ->setParameter(3, $time_str);
        $qb->execute();
        // catch exceptions (these should not be hit because of the validations earlier in this method)
      } catch (\PDOException $e) {
        $app['session']->getFlashBag()->add('errors', "Tweet is not valid.");
        return new Response('error '.$e->getMessage());
      } catch (\UniqueConstraintViolationException $e) {
        $app['session']->getFlashBag()->add('errors', "Tweet is not valid.");
        return new Response('error '.$e->getMessage());
      } catch (\Exception $e) {
        $app['session']->getFlashBag()->add('errors', "Tweet is not valid.");
        return new Response('error '.$e->getMessage());
      }
    }

    // redirect to the index route
    return $app->redirect('/web');
    // return new Response("thanks!", 201);
  }

  // DELETE route to delete a tweet
  public function destroy (Application $app, Request $request, $id) {

    // using Doctrine QueryBuilder, destroy the tweet from the tweets table; the tweet ID is passed as a ULR parameter
    $qb = $this->conn->createQueryBuilder();
    $qb
      ->delete('tweet')
      ->where('id = ?')
      ->setParameter(0, $id);
    $qb->execute();

    // redirect to the index route
    return $app->redirect('/web');
  }

}
