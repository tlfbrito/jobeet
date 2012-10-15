<?php
namespace BeUbi\JobeetBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use BeUbi\JobeetBundle\Entity\Category;
 
/**
* Category controller.
*
*/
class CategoryController extends Controller
{
  
  public function showAction($slug, $page)
  {
    $em = $this->getDoctrine()->getEntityManager();

    $category = $em->getRepository('BeUbiJobeetBundle:Category')->findOneBySlug($slug);

    if (!$category) {
      throw $this->createNotFoundException('Unable to find Category entity.');
    }

    $total_jobs = $em->getRepository('BeUbiJobeetBundle:Job')->countActiveJobs($category->getId());
    $jobs_per_page = $this->container->getParameter('max_jobs_on_category');
    $last_page = ceil($total_jobs / $jobs_per_page);
    $previous_page = $page > 1 ? $page - 1 : 1;
    $next_page = $page < $last_page ? $page + 1 : $last_page;

    $category->setActiveJobs($em->getRepository('BeUbiJobeetBundle:Job')->getActiveJobs($category->getId(), $jobs_per_page, ($page - 1) * $jobs_per_page));

    return $this->render('BeUbiJobeetBundle:Category:show.html.twig', array(
      'category' => $category,
      'last_page' => $last_page,
      'previous_page' => $previous_page,
      'current_page' => $page,
      'next_page' => $next_page,
      'total_jobs' => $total_jobs
    ));
  }
 
}
