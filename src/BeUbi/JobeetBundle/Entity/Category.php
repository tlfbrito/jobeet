<?php

namespace BeUbi\JobeetBundle\Entity;

use BeUbi\JobeetBundle\Utils\Jobeet;
use Doctrine\ORM\Mapping as ORM;

/**
 * BeUbi\JobeetBundle\Entity\Category
 */
class Category
{
    /**
     * @var integer $id
     */
    private $id;
    private $more_jobs;

    /**
     * Get Category Name
     *
     * @return integer 
     */
    public function __toString()
    {
      return $this->getName();
    }
    /**
     * Get id
     *
     * @return integer 
     */
    public function getId()
    {
        return $this->id;
    }
    /**
     * @var string $name
     */
    private $name;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $jobs;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $category_affiliates;
    
    private $active_jobs;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->jobs = new \Doctrine\Common\Collections\ArrayCollection();
        $this->category_affiliates = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Set name
     *
     * @param string $name
     * @return Category
     */
    public function setName($name)
    {
        $this->name = $name;
    
        return $this;
    }

    /**
     * Get name
     *
     * @return string 
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * Add jobs
     *
     * @param BeUbi\JobeetBundle\Entity\Job $jobs
     * @return Category
     */
    public function addJob(\BeUbi\JobeetBundle\Entity\Job $jobs)
    {
        $this->jobs[] = $jobs;
    
        return $this;
    }

    /**
     * Remove jobs
     *
     * @param BeUbi\JobeetBundle\Entity\Job $jobs
     */
    public function removeJob(\BeUbi\JobeetBundle\Entity\Job $jobs)
    {
        $this->jobs->removeElement($jobs);
    }

    /**
     * Get jobs
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getJobs()
    {
        return $this->jobs;
    }

    /**
     * Get category_affiliates
     *
     * @return Doctrine\Common\Collections\Collection 
     */
    public function getCategoryAffiliates()
    {
        return $this->category_affiliates;
    }
    
    public function setActiveJobs($jobs)
    {
      $this->active_jobs = $jobs;
    }

    public function getActiveJobs()
    {
      return $this->active_jobs;
    }
    
    public function getSlug()
    {
      return Jobeet::slugify($this->getName());
    }
    
    public function setMoreJobs($jobs)
    {
      $this->more_jobs = $jobs >=  0 ? $jobs : 0;
    }

    public function getMoreJobs()
    {
      return $this->more_jobs;
    }
    /**
     * @var string $slug
     */
    private $slug;


    /**
     * Set slug
     *
     * @param string $slug
     * @return Category
     */
    public function setSlug($slug)
    {
        $this->slug = $slug;
    
        return $this;
    }
    /**
     * @ORM\PrePersist
     */
    public function setSlugValue()
    {
        $this->slug = Jobeet::slugify($this->getName());
    }
    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $affiliates;


    /**
     * Add affiliates
     *
     * @param \BeUbi\JobeetBundle\Entity\Affiliate $affiliates
     * @return Category
     */
    public function addAffiliate(\BeUbi\JobeetBundle\Entity\Affiliate $affiliates)
    {
        $this->affiliates[] = $affiliates;
    
        return $this;
    }

    /**
     * Remove affiliates
     *
     * @param \BeUbi\JobeetBundle\Entity\Affiliate $affiliates
     */
    public function removeAffiliate(\BeUbi\JobeetBundle\Entity\Affiliate $affiliates)
    {
        $this->affiliates->removeElement($affiliates);
    }

    /**
     * Get affiliates
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getAffiliates()
    {
        return $this->affiliates;
    }
}