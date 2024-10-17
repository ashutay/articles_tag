<?php

namespace App\Tests\Controller;

use App\Entity\Article;
use App\Entity\ArticleTag;
use App\Entity\Tag;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class ArticleControllerTest extends WebTestCase
{
    public function testShowInvalidId()
    {
        $client = static::createClient();
        $client->request('GET', '/api/articles/invalid-id');

        $this->assertEquals(Response::HTTP_BAD_REQUEST, $client->getResponse()->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['message' => 'Invalid article ID']),
            $client->getResponse()->getContent()
        );
    }

    public function testShowArticleNotFound()
    {
        $client = static::createClient();
        $client->request('GET', '/api/articles/0');

        $this->assertEquals(Response::HTTP_NOT_FOUND, $client->getResponse()->getStatusCode());
        $this->assertJsonStringEqualsJsonString(
            json_encode(['message' => 'Article not found']),
            $client->getResponse()->getContent()
        );
    }
}

