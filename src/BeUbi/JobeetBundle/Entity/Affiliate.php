<?php

namespace BeUbi\JobeetBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * BeUbi\JobeetBundle\Entity\Affiliate
 */
class Affiliate
{
    /**
     * @var integer $id
     */
    private $id;


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
     * @var string $url
     */
    private $url;

    /**
     * @var string $email
     */
    private $email;

    /**
     * @var string $token
     */
    private $token;

    /**
     * @var \DateTime $created_at
     */
    private $created_at;

    /**
     * @var \Doctrine\Common\Collections\ArrayCollection
     */
    private $category_affiliates;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->category_affiliates = new \Doctrine\Common\Collections\ArrayCollection();
    }
    
    /**
     * Set url
     *
     * @param string $url
     * @return Affiliate
     */
    public function setUrl($url)
    {
        $this->url = $url;
    
        return $this;
    }

    /**
     * Get url
     *
     * @return string 
     */
    public function getUrl()
    {
        return $this->url;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return Affiliate
     */
    public function setEmail($email)
    {
        $this->email = $email;
    
        return $this;
    }

    /**
     * Get email
     *
     * @return string 
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set token
     *
     * @param string $token
     * @return Affiliate
     */
    public function setToken($token)
    {
        $this->token = $token;
    
        return $this;
    }

    /**
     * Get token
     *
     * @return string 
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     * @return Affiliate
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
    
        return $this;
    }

    /**
     * Get created_at
     *
     * @return \DateTime 
     */
    public function getCreatedAt()
    {
        return $this->created_at;
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
    /**
     * @ORM\PrePersist
     */
    public function setCreatedAtValue()
    {
        if (!$this->getCreatedAt())
        {
          $this->created_at = new \DateTime();
        }

    }
    /**
     * @var boolean
     */
    private $is_active;

    /**
     * @var \Doctrine\Common\Collections\Collection
     */
    private $categories;


    /**
     * Set is_active
     *
     * @param boolean $isActive
     * @return Affiliate
     */
    public function setIsActive($isActive)
    {
        $this->is_active = $isActive;
    
        return $this;
    }

    /**
     * Get is_active
     *
     * @return boolean 
     */
    public function getIsActive()
    {
        return $this->is_active;
    }

    /**
     * Add categories
     *
     * @param \BeUbi\JobeetBundle\Entity\Category $categories
     * @return Affiliate
     */
    public function addCategorie(\BeUbi\JobeetBundle\Entity\Category $categories)
    {
        $this->categories[] = $categories;
    
        return $this;
    }

    /**
     * Remove categories
     *
     * @param \BeUbi\JobeetBundle\Entity\Category $categories
     */
    public function removeCategorie(\BeUbi\JobeetBundle\Entity\Category $categories)
    {
        $this->categories->removeElement($categories);
    }

    /**
     * Get categories
     *
     * @return \Doctrine\Common\Collections\Collection 
     */
    public function getCategories()
    {
        return $this->categories;
    }
}