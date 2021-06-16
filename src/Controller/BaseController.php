<?php
namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;


class BaseController extends AbstractController
{
    /**
     * The folder where relevant templates are located.
     *
     * @var string
     */
    protected $templateFolder;


    public function __construct() {
    }

}