<?php

// This file is auto-generated, don't edit it. Thanks.

namespace AlibabaCloud\SDK\Ocrapi\V20210707\Models;

use AlibabaCloud\Tea\Model;
use GuzzleHttp\Psr7\Stream;

class RecognizeTableOcrRequest extends Model
{
    /**
     * @var bool
     */
    public $lineLess;

    /**
     * @var bool
     */
    public $needRotate;

    /**
     * @var bool
     */
    public $skipDetection;

    /**
     * @var string
     */
    public $url;

    /**
     * @var Stream
     */
    public $body;
    protected $_name = [
        'lineLess'      => 'LineLess',
        'needRotate'    => 'NeedRotate',
        'skipDetection' => 'SkipDetection',
        'url'           => 'Url',
        'body'          => 'body',
    ];

    public function validate()
    {
    }

    public function toMap()
    {
        $res = [];
        if (null !== $this->lineLess) {
            $res['LineLess'] = $this->lineLess;
        }
        if (null !== $this->needRotate) {
            $res['NeedRotate'] = $this->needRotate;
        }
        if (null !== $this->skipDetection) {
            $res['SkipDetection'] = $this->skipDetection;
        }
        if (null !== $this->url) {
            $res['Url'] = $this->url;
        }
        if (null !== $this->body) {
            $res['body'] = $this->body;
        }

        return $res;
    }

    /**
     * @param array $map
     *
     * @return RecognizeTableOcrRequest
     */
    public static function fromMap($map = [])
    {
        $model = new self();
        if (isset($map['LineLess'])) {
            $model->lineLess = $map['LineLess'];
        }
        if (isset($map['NeedRotate'])) {
            $model->needRotate = $map['NeedRotate'];
        }
        if (isset($map['SkipDetection'])) {
            $model->skipDetection = $map['SkipDetection'];
        }
        if (isset($map['Url'])) {
            $model->url = $map['Url'];
        }
        if (isset($map['body'])) {
            $model->body = $map['body'];
        }

        return $model;
    }
}
