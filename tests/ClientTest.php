<?php

use PHPUnit\Framework\TestCase;

class ClientTest extends TestCase
{

    public \Xiaosongshu\Elasticsearch\ESClient $handler;

    /**
     * 初始化，创建客户端
     * @return void
     */
    public function setUp(): void
    {
        parent::setUp();
        /** 创建一个客户端 */
        $this->handler = $this->createMock(\Xiaosongshu\Elasticsearch\ESClient::class);
    }

    /**
     * 验证创建索引
     * @return void
     */
    public function testCreateIndex(){
        $createIndex  = $this->handler->createIndex('index','_doc');
        var_dump($createIndex);


        self::assertIsArray($createIndex);

    }

    /**
     * 验证删除脚本返回值是bool
     * @return void
     */
    public function testDeleteScript(){
        $deleteScript=$this->handler->deleteScript('update_content');
        self::assertIsBool($deleteScript);
        self::assertEquals(false,$deleteScript);
    }

    /**
     * 测试添加脚本返回值是数组
     * @return void
     */
    public function testAddScript(){
        self::assertIsArray($this->handler->addScript('update_content',"doc['title'].value+'_'+'谁不说按家乡好'"));
    }

    /**
     * 测试获取脚本
     * @return void
     */
    public function testGetScript(){
        $getScript = $this->handler->getScript('update_content');
        self::assertIsArray($getScript);
    }


}