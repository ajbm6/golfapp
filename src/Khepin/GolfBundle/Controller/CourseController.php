<?php

namespace Khepin\GolfBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Template;
use Khepin\GolfBundle\Form\CourseType;
use Symfony\Component\HttpFoundation\Request;
use Khepin\GolfBundle\Entity\Hole;
use Khepin\GolfBundle\Entity\Course;

class CourseController extends Controller {

    /**
     * Show form to create a new Golf Course
     * 
     * @Route("/course/add", name="course_add")
     * @Template()
     */
    public function addAction() {
        $form = $this->createForm(new CourseType());
        return array('form' => $form->createView());
    }
    
    /**
     * Save the form (if form is valid) or redirect to add
     * 
     * @Route("/course/create", name="course_create")
     * @Template("KhepinGolfBundle:Course:add.html.twig")
     */
    public function createAction(Request $request) {
        $form = $this->createForm(new CourseType());
        $form->bindRequest($request);
        if($form->isValid()){
            $course = $form->getData();
            $em = $this->getDoctrine()->getEntityManager();
            $em->persist($course);
            $em->flush();
            return $this->redirect($this->generateUrl('course_setpars', array('id' => $course->getId())));
        }
        return array('form' => $form->createView());
    }
    
    /**
     * @Route("/course/show/{id}", name="course_show")
     * @Template()
     * @param type $id 
     */
    public function showAction($id){
        $course = $this->getDoctrine()->getRepository('KhepinGolfBundle:Course')->find($id);
        return array('course' => $course);
    }
    
    /**
     * @Route("/course/setpars/{id}", name="course_setpars")
     * @Template()
     * @param integer $id 
     */
    public function setparsAction($id){
        $course = $this->getDoctrine()->getRepository('KhepinGolfBundle:Course')->find($id);
        $form = $this->createForm(new \Khepin\GolfBundle\Form\ParSetType($course));
        
        return array('form' => $form->createView(), 'course' => $course);
    }
    
    /**
     * @Route("/course/savepars/", name="save_pars")
     * @Template("KhepinGolfBundle:Course:setpars.html.twig")
     */
    public function saveparsAction(Request $request){
        $data = $request->get('khepin_golfbundle_parsettype');
        $course_id = $data['course'];
        $course = $this->getDoctrine()->getRepository('KhepinGolfBundle:Course')
                ->find($course_id);
        $form = $this->createForm(new \Khepin\GolfBundle\Form\ParSetType($course));
        $form->bindRequest($request);
        
        if($form->isValid()) {
            // Remove the csrf token before iterating on children
            $form->get('holes')->remove('_token');
            // Get the entity manager
            $em = $this->getDoctrine()->getEntityManager();
            foreach($form->get('holes') as $hole_form) {
                $hole = $hole_form->getData();
                $em->persist($hole);
            }
            $em->flush();
            return new \Symfony\Component\HttpFoundation\Response('haha Course: '.$course->getId());
        }
        return array('form' => $form->createView(), 'course' => $course);
    }
}
