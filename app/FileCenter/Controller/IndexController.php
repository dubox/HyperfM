<?php

declare(strict_types=1);
/**
 * This file is part of Hyperf.
 *
 * @link     https://www.hyperf.io
 * @document https://hyperf.wiki
 * @contact  group@hyperf.io
 * @license  https://github.com/hyperf/hyperf/blob/master/LICENSE
 */

namespace App\FileCenter\Controller;

use App\Common\Exception\BusinessException;
use App\Common\Library\FileCenter\File;
use App\Common\Library\Log\Log;
use App\FileCenter\Constants\BusinessErrorCode;
use App\FileCenter\Model\FileModel;
use App\FileCenter\Model\FileSystemModel;
use GuzzleHttp\Client;
use Hyperf\HttpMessage\Stream\SwooleStream;
use Hyperf\HttpServer\Response;

class IndexController extends AbstractController
{
    public function index()
    {
        $user = $this->request->input('user', 'Hyperf');
        $method = $this->request->getMethod();

        Log::debug('debug log');

        return $this->success(data: ['method' => $method, 'message' => "Hello {$user}."]);
    }


    public function add()
    {
        $data = $this->request->all();

        // 文本、图片、文档、语音、视频
        $mineType = ['text', 'image', 'application', 'audio', 'video'];
        if (empty($data['mimeType']) || !in_array(explode('/', $data['mimeType'])[0], $mineType)) {
            throw new BusinessException(BusinessErrorCode::CUSTOM_ERROR, "文件信息有误，请重新提交");
        }

        // 校验filesystemKey
        if (FileSystemModel::where('key', $data['filesystemKey'])->doesntExist()) {
            throw new BusinessException(BusinessErrorCode::CUSTOM_ERROR, "文件系统信息有误，请重新提交");
        }

        $fileM = new FileModel();
        if ($fileM->fill($data)->save())
            return $this->success(data: ['id' => $fileM->id]);

        throw new BusinessException(BusinessErrorCode::SERVER_ERROR);
    }

    public function filesystemList()
    {
        return $this->success(data: make(FileSystemModel::class)->get());
    }

    public function getFileContent(Response $response)
    {
        $fileId = $this->request->input('fileId');
        if (empty($fileId)) {
            throw new BusinessException(BusinessErrorCode::PARAM_ERROR, '文件Id');
        }

        $fileInfo = File::get($fileId);
        if (empty($fileInfo)) {
            throw new BusinessException(BusinessErrorCode::DATA_LACK, '文件');
        }

        $fileUrl = $fileInfo->fullPath() ?? '';
        if (empty($fileUrl)) {
            throw new BusinessException(BusinessErrorCode::DATA_LACK, '文件');
        }

        $nameKey = $this->request->input('nameKey', 'name');
        $fileName = $fileInfo->$nameKey ?? "";

        $client = new Client();
        $responseBody = $client->request('GET', $fileUrl)->getBody()->getContents();

        $headers = [
            'Content-Type' => $fileInfo->mimeType,
            'Content-Disposition' => 'inline; filename=' . $fileName,
        ];

        $body = new SwooleStream($responseBody);
        return $response->withHeaders($headers)->withBody($body);
    }
}
