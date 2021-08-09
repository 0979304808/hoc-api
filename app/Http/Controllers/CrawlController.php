<?php

namespace App\Http\Controllers;

use Couchbase\Document;
use Goutte;
use Illuminate\Support\Facades\Request;
use  PHPOnCouch\CouchClient;
use  PHPOnCouch\CouchDocument;

class CrawlController extends Controller
{
    // Lấy tất cả dữ liệu
    public function index()
    {

        $client = new CouchClient('http://127.0.0.1:5984', 'demo-1', ['username' => 'admin', 'password' => 'hoanghung1']);
        $data = [
            "title" => 'bai viet 1'

        ];
        dd( $client->find((object)$data) );
        $view = $client->seletor('')->include_docs(TRUE)->limit(2)->getView('new-design', 'new-view');

        return response()->json(['data' => $view['rows']]);
    }

    // Tạo bảng dữ liệu
    public function createTable()
    {
        $client = new CouchClient('http://127.0.0.1:5984', 'demo-1', ['username' => 'admin', 'password' => 'hoanghung1']);
        $view = $client->listDatabases();
    }

    // Lấy ra 1 đối tượng
    public function show($id)
    {
        $client = new CouchClient('http://127.0.0.1:5984', 'demo-1', ['username' => 'admin', 'password' => 'hoanghung1']);
        $doc = $client->getDoc($id);
        return response(['data' => $doc]);
    }

    // Tạo mới 1 đối tượng
    public function addDocument()
    {
        $client = new CouchClient('http://127.0.0.1:5984', 'demo-1', ['username' => 'admin', 'password' => 'hoanghung1']);
        $doc = new CouchDocument($client);
        $doc->title = 'bai viet dau tien';
        $doc->description = 'hello word';
        return response(['data' => $doc->getFields()]);
    }

    // Sửa 1 đối tượng
    public function updateDocument(Request $request, $id)
    {
        $client = new CouchClient('http://127.0.0.1:5984', 'demo-1', ['username' => 'admin', 'password' => 'hoanghung1']);
        $client->title = request()->get('title');
        $client->description = request()->get('description');
        $data = $client->updateDoc($id, 'abc', $client);
        return response(['data' => $data]);
    }

    // Xóa 1 đối tượng
    public function deleteDocument($id)
    {
        $client = new CouchClient('http://127.0.0.1:5984', 'demo-1', ['username' => 'admin', 'password' => 'hoanghung1']);
        $doc = $client->getDoc($id);
        $data = $client->deleteDocs([$doc]);
        return response(['data' => $data]);
    }

}
