<?php

namespace BeUbi\JobeetBundle\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;

class JobControllerTest extends WebTestCase
{
  public function getMostRecentProgrammingJob()
  {
    $kernel = static::createKernel();
    $kernel->boot();
    $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
 
    $query = $em->createQuery('SELECT j from BeUbiJobeetBundle:Job j LEFT JOIN j.category c WHERE c.slug = :slug AND j.expires_at > :date ORDER BY j.created_at DESC');
    $query->setParameter('slug', 'programming');
    $query->setParameter('date', date('Y-m-d H:i:s', time()));
    $query->setMaxResults(1);
    return $query->getSingleResult();
  }
 
  public function getExpiredJob()
  {
    $kernel = static::createKernel();
    $kernel->boot();
    $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');
 
    $query = $em->createQuery('SELECT j from BeUbiJobeetBundle:Job j WHERE j.expires_at < :date');
    $query->setParameter('date', date('Y-m-d H:i:s', time()));
    $query->setMaxResults(1);
    return $query->getSingleResult();
  }
 
  public function testIndex()
  {
    // get the custom parameters from app config.yml
    $kernel = static::createKernel();
    $kernel->boot();
    $max_jobs_on_homepage = $kernel->getContainer()->getParameter('max_jobs_on_homepage');
    $max_jobs_on_category = $kernel->getContainer()->getParameter('max_jobs_on_category');
 
    $client = static::createClient();
 
    $crawler = $client->request('GET', '/');
    $this->assertEquals('BeUbi\JobeetBundle\Controller\JobController::indexAction', $client->getRequest()->attributes->get('_controller'));
 
    // expired jobs are not listed
    $this->assertTrue($crawler->filter('.jobs td.position:contains("Expired")')->count() == 0);
 
    // only $max_jobs_on_homepage jobs are listed for a category
    $this->assertTrue($crawler->filter('.category_programming tr')->count() == 10);
    $this->assertTrue($crawler->filter('.category_design .more_jobs')->count() == 0);
    $this->assertTrue($crawler->filter('.category_programming .more_jobs')->count() == 1);
 
    // jobs are sorted by date
    $this->assertTrue($crawler->filter('.category_programming tr')->first()->filter(sprintf('a[href*="/%d/"]', $this->getMostRecentProgrammingJob()->getId()))->count() == 1);
 
    // each job on the homepage is clickable and give detailed information
    $job = $this->getMostRecentProgrammingJob();
    $link = $crawler->selectLink('Web Developer')->first()->link();
    $crawler = $client->click($link);
    $this->assertEquals('BeUbi\JobeetBundle\Controller\JobController::showAction', $client->getRequest()->attributes->get('_controller'));
    $this->assertEquals($job->getCompanySlug(), $client->getRequest()->attributes->get('company'));
    $this->assertEquals($job->getLocationSlug(), $client->getRequest()->attributes->get('location'));
    $this->assertEquals($job->getPositionSlug(), $client->getRequest()->attributes->get('position'));
    $this->assertEquals($job->getId(), $client->getRequest()->attributes->get('id'));
 
    // a non-existent job forwards the user to a 404
    $crawler = $client->request('GET', '/job/foo-inc/milano-italy/0/painter');
    $this->assertTrue(404 === $client->getResponse()->getStatusCode());
 
    // an expired job page forwards the user to a 404
    $crawler = $client->request('GET', sprintf('/job/sensio-labs/paris-france/%d/web-developer', $this->getExpiredJob()->getId()));
    $this->assertTrue(404 === $client->getResponse()->getStatusCode());
  }

  public function testJobForm()
  {
    $client = static::createClient();

    $crawler = $client->request('GET', '/job/new');
    $this->assertEquals('BeUbi\JobeetBundle\Controller\JobController::newAction', $client->getRequest()->attributes->get('_controller'));
  
    $form = $crawler->selectButton('Preview your job')->form(array(
      'job[company]'      => 'Sensio Labs',
      'job[url]'          => 'http://www.sensio.com/',
      'job[file]'         => __DIR__.'/../../../../../web/bundles/beubijobeet/images/sensio-labs.gif',
      'job[position]'     => 'Developer',
      'job[location]'     => 'Atlanta, USA',
      'job[description]'  => 'You will work with symfony to develop websites for our customers.',
      'job[how_to_apply]' => 'Send me an email',
      'job[email]'        => 'for.a.job@example.com',
      'job[is_public]'    => false,
    ));
 
    $client->submit($form);
    $this->assertEquals('BeUbi\JobeetBundle\Controller\JobController::createAction', $client->getRequest()->attributes->get('_controller'));
    
    $client->followRedirect();
    $this->assertEquals('BeUbi\JobeetBundle\Controller\JobController::previewAction', $client->getRequest()->attributes->get('_controller'));

    
    //DataBase Check
    $kernel = static::createKernel();
    $kernel->boot();
    $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');

    $query = $em->createQuery('SELECT count(j.id) from BeUbiJobeetBundle:Job j WHERE j.location = :location AND j.is_activated IS NULL AND j.is_public = 0');
    $query->setParameter('location', 'Atlanta, USA');
    $this->assertTrue(0 < $query->getSingleScalarResult());
  }
  
  public function testJobFormFailure()
  {    
    
    $client = static::createClient();
    $crawler = $client->request('GET', '/job/new');
    $form = $crawler->selectButton('Preview your job')->form(array(
      'job[company]'      => 'Sensio Labs',
      'job[url]'          => 'http://www.sensio.com/',
      'job[position]'     => 'Developer',
      'job[location]'     => 'Atlanta, USA',
      'job[email]'        => 'non-valid.email',
     'job[is_public]'    => false,
    ));


    $crawler = $client->submit($form);
    //echo $crawler->filter('.error_list')->count();
    // check if we have 3 errors
    $this->assertTrue($crawler->filter('.error_list')->count() == 3);
    // check if we have error on job_description field
    $this->assertTrue($crawler->filter('#job_description')->siblings()->first()->filter('.error_list')->count() == 1);
    // check if we have error on job_how_to_apply field
    $this->assertTrue($crawler->filter('#job_how_to_apply')->siblings()->first()->filter('.error_list')->count() == 1);
    // check if we have error on job_email field
    $this->assertTrue($crawler->filter('#job_email')->siblings()->first()->filter('.error_list')->count() == 1);
  
  }
   
  
  public function createJob($values = array(), $publish = false)
  {
    $client = static::createClient();
    $crawler = $client->request('GET', '/job/new');
    $form = $crawler->selectButton('Preview your job')->form(array_merge(array(
      'job[company]'      => 'Sensio Labs',
      'job[url]'          => 'http://www.sensio.com/',
      'job[position]'     => 'Developer',
      'job[location]'     => 'Atlanta, USA',
      'job[description]'  => 'You will work with symfony to develop websites for our customers.',
      'job[how_to_apply]' => 'Send me an email',
      'job[email]'        => 'for.a.job@example.com',
      'job[is_public]'    => false,
    ), $values));

    $client->submit($form);
    $client->followRedirect();

    if($publish) {
      $crawler = $client->getCrawler();
      $form = $crawler->selectButton('Publish')->form();
      $client->submit($form);
      $client->followRedirect();
    }

    return $client;
  }

  public function getJobByPosition($position)
  {
    $kernel = static::createKernel();
    $kernel->boot();
    $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');

    $query = $em->createQuery('SELECT j from BeUbiJobeetBundle:Job j WHERE j.position = :position');
    $query->setParameter('position', $position);
    $query->setMaxResults(1);
    return $query->getSingleResult();
  }
  
  public function testPublishJob()
  {
    $client = $this->createJob(array('job[position]' => 'FOO1'));
    $crawler = $client->getCrawler();
    $form = $crawler->selectButton('Publish')->form();
    $client->submit($form);

    $kernel = static::createKernel();
    $kernel->boot();
    $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');

    $query = $em->createQuery('SELECT count(j.id) from BeUbiJobeetBundle:Job j WHERE j.position = :position AND j.is_activated = 1');
    $query->setParameter('position', 'FOO1');
    $this->assertTrue(0 < $query->getSingleScalarResult());
  }
  
  //If a job is published, the edit page must return a 404 status code.
  public function testEditJob()
  {
    $client = $this->createJob(array('job[position]' => 'FOO3'), true);
    $crawler = $client->getCrawler();
    $crawler = $client->request('GET', sprintf('/job/%s/edit', $this->getJobByPosition('FOO3')->getToken()));
    $this->assertTrue(404 === $client->getResponse()->getStatusCode());
  }
  
  public function testDeleteJob()
  {
    $client = $this->createJob(array('job[position]' => 'FOO2'));
    $crawler = $client->getCrawler();
    $form = $crawler->selectButton('Delete')->form();
    $client->submit($form);

    $kernel = static::createKernel();
    $kernel->boot();
    $em = $kernel->getContainer()->get('doctrine.orm.entity_manager');

    $query = $em->createQuery('SELECT count(j.id) from BeUbiJobeetBundle:Job j WHERE j.position = :position');
    $query->setParameter('position', 'FOO2');
    $this->assertTrue(0 == $query->getSingleScalarResult());
  }
    /*
    public function testCompleteScenario()
    {
        // Create a new client to browse the application
        $client = static::createClient();

        // Create a new entry in the database
        $crawler = $client->request('GET', '/beubi_job/');
        $this->assertTrue(200 === $client->getResponse()->getStatusCode());
        $crawler = $client->click($crawler->selectLink('Create a new entry')->link());

        // Fill in the form and submit it
        $form = $crawler->selectButton('Create')->form(array(
            'job[field_name]'  => 'Test',
            // ... other fields to fill
        ));

        $client->submit($form);
        $crawler = $client->followRedirect();

        // Check data in the show view
        $this->assertTrue($crawler->filter('td:contains("Test")')->count() > 0);

        // Edit the entity
        $crawler = $client->click($crawler->selectLink('Edit')->link());

        $form = $crawler->selectButton('Edit')->form(array(
            'job[field_name]'  => 'Foo',
            // ... other fields to fill
        ));

        $client->submit($form);
        $crawler = $client->followRedirect();

        // Check the element contains an attribute with value equals "Foo"
        $this->assertTrue($crawler->filter('[value="Foo"]')->count() > 0);

        // Delete the entity
        $client->submit($crawler->selectButton('Delete')->form());
        $crawler = $client->followRedirect();

        // Check the entity has been delete on the list
        $this->assertNotRegExp('/Foo/', $client->getResponse()->getContent());
    }
    */
}