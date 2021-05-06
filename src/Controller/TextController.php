<?php

namespace App\Controller;

use App\Model\Text;
use App\Resource\TextResource;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

use App\Repository\TextRepository;

class TextController extends AbstractController
{
    /**
     * @Route("/text", name="text")
     */
    public function index(Request $request, ContainerInterface $container): Response
    {

        //        $text = TextRepository::find(69108);
//        $res = new TextResource($text);
//        print_r($res->toJson());

        //print_r($text->era->name);

        /*
        $texts = TextRepository::queryAll()->where('text_id', '<', 100)->with(['scripts','forms'])->chunk(20, function($res) {
            foreach ($res as $text) {
                $res = new TextResource($text);
                print_r($res->toJson());
            }
        });
        */

        //$text = TextResource::collection();
//        $texts = TextRepository::findAll(5);
//        foreach( $texts as $text ) {
//            $ret = $text->toArray(null);
//            print_r($ret);
//        }


        return $this->render('text/index.html.twig', [
            'controller_name' => 'TextController',
        ]);
    }
}
