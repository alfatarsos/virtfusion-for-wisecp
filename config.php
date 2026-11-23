<?php
return [
    'type'                        => 'virtualization',
    'access-hash'                 => true,
    'server-info-checker'         => true,
    'server-info-port'            => true,
    'server-info-not-secure-port' => 443,
    'server-info-secure-port'     => 443,
    'configurable-option-params'  => [
        'package',
        'hypervisor_group',
        'hypervisor_group_manual',
        'template',
        'ipv4_qty',
        'ipv6',
    ],
];
