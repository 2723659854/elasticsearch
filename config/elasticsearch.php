<?php
return [
    /** 节点列表 */
    'nodes' => ['127.0.0.1:9200'],
    /** 用户名 */
    'username'=>'',
    /** 密码 */
    'password'=>'',
    /** 代理 */
    'proxy'=>[
        'client' => [
            'curl' => [
                CURLOPT_PROXY => '', // 明确禁用代理
                CURLOPT_PROXYUSERPWD => '', // 清除代理认证
                CURLOPT_NOPROXY => '*' // 对所有主机禁用代理
            ]
        ]
    ]
];