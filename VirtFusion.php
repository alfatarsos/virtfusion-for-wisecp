<?php
/**
 * VirtFusion Server Module for WiseCP
 *
 * @author    Tiago Pereira
 * @copyright Copyright (c) 2026 Tiago Pereira
 * @version   1.0.0
 * @link      https://docs.virtfusion.com/api/
 * @license   MIT
 */
class VirtFusion_Module extends ServerModule
{
    public $api;
    public $entity_id_name = 'server_id';
    private $_panel_host = '';

    public function __construct($server = [], $options = [])
    {
        $this->_name = __CLASS__;
        parent::__construct($server, $options);
    }

    protected function define_server_info($server = [])
    {
        if (!class_exists('VirtFusionAPI')) {
            include __DIR__ . DS . 'api.php';
        }

        // Priority: hostname > IP (configure your VirtFusion panel hostname in WiseCP Server Settings)
        $host = !empty($server['host']) ? $server['host'] : ($server['ip'] ?? '');

        $this->_panel_host = $host;
        $this->api         = new VirtFusionAPI(
            $host,
            $server['port'] ?? 443,
            $server['secure'] ?? true,
            $server['access_hash'] ?? ''
        );
    }

    public function testConnect()
    {
        $connect = $this->api->call('GET', 'connect');
        if ($connect === false) {
            $this->error = $this->api->error ?: 'API connection failed';
            return false;
        }
        return true;
    }

    public function config_options($data = [])
    {
        $packages         = [0 => ___('needs/none')];
        $hypervisorGroups = [0 => ___('needs/none') . ' (Auto-select first available)'];
        $templates        = [0 => ___('needs/none')];

        $ipv4Options = [
            0 => 'No IPv4 (IPv6 only)',
            1 => '1 IPv4',
            2 => '2 IPv4',
            3 => '3 IPv4',
            4 => '4 IPv4',
        ];

        $pkgResult = $this->api->getPackages();
        if ($pkgResult && isset($pkgResult['data']) && is_array($pkgResult['data'])) {
            foreach ($pkgResult['data'] as $pkg) {
                if (isset($pkg['id'])) {
                    $packages[$pkg['id']] = ($pkg['name'] ?? ('Package #' . $pkg['id']))
                        . (($pkg['enabled'] ?? true) ? '' : ' (disabled)');
                }
            }
        }

        $hgResult = $this->api->getHypervisorGroups();
        if ($hgResult && isset($hgResult['data']) && is_array($hgResult['data'])) {
            foreach ($hgResult['data'] as $hg) {
                if (isset($hg['id'])) {
                    $hypervisorGroups[$hg['id']] = $hg['name'] ?? ('Location #' . $hg['id']);
                }
            }
        }

        $selectedPackage = (int)($data['package'] ?? 0);
        if ($selectedPackage > 0) {
            $tplResult = $this->api->getTemplatesByPackage($selectedPackage);
            if ($tplResult && isset($tplResult['data']) && is_array($tplResult['data'])) {
                foreach ($tplResult['data'] as $tpl) {
                    if (isset($tpl['id'])) {
                        $templates[$tpl['id']] = $tpl['name'] ?? ('Template #' . $tpl['id']);
                    }
                }
            }
        }

        $hypervisorGroups[-1] = 'Auto (VirtFusion decides)';

        return [
            'package' => [
                'name'    => 'Package',
                'type'    => 'dropdown',
                'width'   => '50',
                'options' => $packages,
                'value'   => $data['package'] ?? '',
            ],
            'hypervisor_group' => [
                'name'        => 'Location (Hypervisor Group)',
                'description' => 'Select a location, use "Auto" to let VirtFusion decide, or use manual ID below',
                'type'        => 'dropdown',
                'width'       => '50',
                'options'     => $hypervisorGroups,
                'value'       => $data['hypervisor_group'] ?? '-1',
            ],
            'hypervisor_group_manual' => [
                'name'        => 'Manual Location ID',
                'description' => 'Only use if dropdown above is empty. Enter the hypervisor group ID from VirtFusion. Use -1 for auto.',
                'type'        => 'text',
                'width'       => '50',
                'value'       => $data['hypervisor_group_manual'] ?? '',
                'placeholder' => 'e.g. 1 or -1 for auto',
            ],
            'template' => [
                'name'    => 'Default Operating System',
                'type'    => 'dropdown',
                'width'   => '50',
                'options' => $templates,
                'value'   => $data['template'] ?? '',
            ],
            'ipv4_qty' => [
                'name'        => 'IPv4 Quantity',
                'description' => 'Number of IPv4 addresses to allocate',
                'type'        => 'dropdown',
                'width'       => '50',
                'options'     => $ipv4Options,
                'value'       => $data['ipv4_qty'] ?? 1,
            ],
            'ipv6' => [
                'name'    => 'Enable IPv6',
                'type'    => 'approval',
                'checked' => $data['ipv6'] ?? false,
            ],
        ];
    }

    private function getClientData()
    {
        $email    = '';
        $name     = '';
        $clientId = 0;

        if (isset($this->order['owner_id'])) {
            $clientId = (int)$this->order['owner_id'];
        } elseif (isset($this->user['id'])) {
            $clientId = (int)$this->user['id'];
        }

        if (isset($this->user) && is_array($this->user)) {
            if (!empty($this->user['email'])) {
                $email = $this->user['email'];
            }
            if (!empty($this->user['name'])) {
                $name = trim(($this->user['name'] ?? '') . ' ' . ($this->user['surname'] ?? ''));
            }
            if (empty($name) && !empty($this->user['full_name'])) {
                $name = $this->user['full_name'];
            }
        }

        if (isset($this->order['owner_data']) && is_array($this->order['owner_data'])) {
            $od = $this->order['owner_data'];
            if (empty($email) && !empty($od['email'])) {
                $email = $od['email'];
            }
            if (empty($name)) {
                $name = trim(($od['name'] ?? '') . ' ' . ($od['surname'] ?? ''));
                if (empty($name)) {
                    $name = $od['full_name'] ?? $od['company_name'] ?? '';
                }
            }
        }

        if (empty($name) && !empty($email)) {
            $name = ucfirst(explode('@', $email)[0]);
        }
        if (empty($name)) {
            $name = 'Customer ' . ($this->order['id'] ?? random_int(1000, 9999));
        }

        return [
            'email'     => trim((string)$email),
            'name'      => trim((string)$name),
            'client_id' => $clientId,
        ];
    }

    public function create(array $order_options = [])
    {
        $serviceId     = (int)($this->order['id'] ?? 0);
        $password      = $this->generatePassword();
        $creation_info = $order_options['creation_info'] ?? [];

        $clientData = $this->getClientData();
        if (empty($clientData['email'])) {
            $this->error = 'Client email not found';
            return false;
        }

        $packageId         = (int)($creation_info['package'] ?? 0);
        $hypervisorGroupId = $creation_info['hypervisor_group'] ?? null;
        $templateId        = (int)($creation_info['template'] ?? 0);
        $ipv4Qty           = (int)($creation_info['ipv4_qty'] ?? 1);
        $enableIpv6        = !empty($creation_info['ipv6']);

        if ($hypervisorGroupId !== null && $hypervisorGroupId !== '') {
            $hypervisorGroupId = (int)$hypervisorGroupId;
        } else {
            $hypervisorGroupId = null;
        }

        if (($hypervisorGroupId === null || $hypervisorGroupId === 0) && !empty($creation_info['hypervisor_group_manual'])) {
            $hypervisorGroupId = (int)$creation_info['hypervisor_group_manual'];
        }

        // Fallbacks for WiseCP versions storing options in different properties
        foreach ([$this->options ?? [], $this->config ?? [], $this->order['product_data'] ?? []] as $src) {
            if (!is_array($src)) {
                continue;
            }
            if ($packageId <= 0 && isset($src['package'])) {
                $packageId = (int)$src['package'];
            }
            if (($hypervisorGroupId === null || $hypervisorGroupId === 0) && isset($src['hypervisor_group'])) {
                $hypervisorGroupId = (int)$src['hypervisor_group'];
            }
            if (($hypervisorGroupId === null || $hypervisorGroupId === 0) && isset($src['hypervisor_group_manual'])) {
                $hypervisorGroupId = (int)$src['hypervisor_group_manual'];
            }
        }

        if ($hypervisorGroupId === null || $hypervisorGroupId === 0) {
            $hypervisorGroupId = -1;
        }

        if ($ipv4Qty <= 0) {
            $enableIpv6 = true;
        }

        if ($packageId <= 0) {
            $this->error = 'Package not configured. Edit product settings in the Core tab.';
            return false;
        }

        if (class_exists('Modules')) {
            Modules::save_log('Servers', 'VirtFusion', 'CREATE_VALUES', [
                'packageId'         => $packageId,
                'hypervisorGroupId' => $hypervisorGroupId,
                'templateId'        => $templateId,
                'ipv4Qty'           => $ipv4Qty,
                'enableIpv6'        => $enableIpv6,
            ], 'Provisioning parameters prepared');
        }

        // Prefer a manually configured VirtFusion userId if provided
        $virtUserId = 0;
        if (!empty($creation_info['user_id'])) {
            $virtUserId = (int)$creation_info['user_id'];
        } elseif (isset($this->options['user_id'])) {
            $virtUserId = (int)$this->options['user_id'];
        } elseif (isset($this->config['user_id'])) {
            $virtUserId = (int)$this->config['user_id'];
        } elseif (isset($this->order['product_data']['user_id'])) {
            $virtUserId = (int)$this->order['product_data']['user_id'];
        }

        if ($virtUserId <= 0) {
            $extRelationId = $clientData['client_id'] ?: null;
            $virtUserId    = $this->api->getOrCreateUser(
                $clientData['email'],
                $clientData['name'],
                $password,
                $extRelationId
            );
        }

        if ($virtUserId <= 0) {
            $this->error = $this->api->error ?: 'Failed to get/create user';
            return false;
        }

        $hostname = !empty($order_options['hostname']) ? $order_options['hostname'] : "vps-{$serviceId}";

        // VirtFusion API expects the Hypervisor Group ID in the 'hypervisorId' field.
        $request = [
            'userId'    => $virtUserId,
            'packageId' => $packageId,
            'hostname'  => $hostname,
            'password'  => $password,
        ];

        if ($hypervisorGroupId > 0) {
            $request['hypervisorId'] = $hypervisorGroupId;
        }
        if ($templateId > 0) {
            $request['templateId'] = $templateId;
        }
        if ($enableIpv6) {
            $request['ipv6'] = true;
        }
        if ($ipv4Qty > 0) {
            $request['ipv4'] = $ipv4Qty;
        }

        $response = $this->api->createServer($request);

        if (!$response || !isset($response['data'])) {
            $this->error = $this->api->error ?: 'Failed to create server';
            return false;
        }

        $serverData = $response['data'];
        $serverId   = (int)($serverData['id'] ?? 0);

        if ($serverId <= 0) {
            $this->error = 'Server created but no ID returned';
            return false;
        }

        if (class_exists('Modules')) {
            Modules::save_log('Servers', 'VirtFusion', 'CREATE_SUCCESS', [
                'server_id' => $serverId,
                'request'   => VirtFusionAPI::sanitizeLogData($request),
            ], 'Server created successfully');
        }

        // Wait briefly for VirtFusion to allocate network interfaces, then fetch updated details
        sleep(3);

        $updatedServer = $this->api->getServer($serverId);
        if ($updatedServer && isset($updatedServer['data'])) {
            $serverData = $updatedServer['data'];
        }

        $dedicatedip = '';
        $assignedIps = [];

        if (isset($serverData['network']['interfaces']) && is_array($serverData['network']['interfaces'])) {
            foreach ($serverData['network']['interfaces'] as $iface) {
                if (isset($iface['ipv4']) && is_array($iface['ipv4'])) {
                    foreach ($iface['ipv4'] as $ip) {
                        $addr = $ip['address'] ?? '';
                        if ($addr) {
                            if (empty($dedicatedip)) {
                                $dedicatedip = $addr;
                            }
                            $assignedIps[] = $addr;
                        }
                    }
                }
                if (isset($iface['ipv6']) && is_array($iface['ipv6'])) {
                    foreach ($iface['ipv6'] as $ip) {
                        $addr = $ip['address'] ?? '';
                        if ($addr) {
                            $assignedIps[] = $addr;
                        }
                    }
                }
            }
        }

        if (empty($dedicatedip) && isset($serverData['network']['ipv4']) && is_array($serverData['network']['ipv4'])) {
            foreach ($serverData['network']['ipv4'] as $ip) {
                $addr = $ip['address'] ?? $ip['ip'] ?? '';
                if ($addr) {
                    if (empty($dedicatedip)) {
                        $dedicatedip = $addr;
                    }
                    $assignedIps[] = $addr;
                }
            }
        }

        return [
            'hostname'     => $hostname,
            'ip'           => $dedicatedip,
            'assigned_ips' => $assignedIps,
            'config'       => [
                'server_id' => $serverId,
                'user_id'   => $virtUserId,
            ],
            'login'        => [
                'username' => 'root',
                'password' => $this->encode_str($password),
            ],
        ];
    }

    public function suspend()
    {
        $serverId = $this->getServerId();
        if (!$serverId) {
            $this->error = 'Server ID not found';
            return false;
        }
        $result = $this->api->suspendServer($serverId);
        if ($result === false) {
            $this->error = $this->api->error ?: 'Failed';
            return false;
        }
        return true;
    }

    public function unsuspend()
    {
        $serverId = $this->getServerId();
        if (!$serverId) {
            $this->error = 'Server ID not found';
            return false;
        }
        $result = $this->api->unsuspendServer($serverId);
        if ($result === false) {
            $this->error = $this->api->error ?: 'Failed';
            return false;
        }
        return true;
    }

    public function terminate()
    {
        $serverId = $this->getServerId();
        if (!$serverId) {
            $this->error = 'Server ID not found';
            return false;
        }
        $result = $this->api->deleteServer($serverId);
        if ($result === false) {
            $this->error = $this->api->error ?: 'Failed';
            return false;
        }
        return true;
    }

    public function get_status()
    {
        return true;
    }

    public function get_service_status()
    {
        return 'active';
    }

    private function getServerId()
    {
        return (int)($this->order['options']['config']['server_id'] ?? $this->config['server_id'] ?? 0);
    }

    private function getUserId()
    {
        return (int)($this->order['options']['config']['user_id'] ?? $this->config['user_id'] ?? 0);
    }

    private function getPanelUrl()
    {
        if (isset($this->api) && method_exists($this->api, 'getBaseUrl') && !empty($this->api->hostname)) {
            return $this->api->getBaseUrl();
        }

        $host = '';
        if (!empty($this->_panel_host)) {
            $host = $this->_panel_host;
        } elseif (!empty($this->server['ip'])) {
            $host = $this->server['ip'];
        }

        $host = trim((string)$host);
        if (empty($host)) {
            return '';
        }

        if (stripos($host, 'http://') === 0 || stripos($host, 'https://') === 0) {
            return rtrim($host, '/');
        }

        return 'https://' . rtrim($host, '/');
    }

    private function mapServerStatus($state)
    {
        $state = strtolower(trim((string)($state ?? 'unknown')));

        $statusMap = [
            'running'    => 'Online',
            'active'     => 'Online',
            'online'     => 'Online',
            'started'    => 'Online',
            'stopped'    => 'Offline',
            'shutoff'    => 'Offline',
            'shutdown'   => 'Offline',
            'offline'    => 'Offline',
            'poweroff'   => 'Offline',
            'allocated'  => 'Provisioning',
            'allocating' => 'Provisioning',
            'building'   => 'Provisioning',
            'creating'   => 'Provisioning',
            'installing' => 'Installing',
            'pending'    => 'Pending',
            'complete'   => 'Online',
            'suspended'  => 'Suspended',
            'paused'     => 'Paused',
            'error'      => 'Error',
            'failed'     => 'Failed',
        ];

        return $statusMap[$state] ?? ucfirst($state);
    }

    private function normalizeHostname($value)
    {
        $h = strtolower(trim((string)$value));
        if (in_array($h, ['', 'null', 'n/a', '-', 'unknown'], true)) {
            return '';
        }
        return trim((string)$value);
    }

    private function extractApiHostname(array $sd)
    {
        $candidates = [
            $sd['hostname'] ?? null,
            $sd['name'] ?? null,
            $sd['settings']['hostname'] ?? null,
            $sd['settings']['name'] ?? null,
            $sd['config']['hostname'] ?? null,
            $sd['os']['hostname'] ?? null,
            $sd['meta']['hostname'] ?? null,
            $sd['server']['hostname'] ?? null,
            $sd['data']['hostname'] ?? null,
        ];

        foreach ($candidates as $candidate) {
            $normalized = $this->normalizeHostname($candidate);
            if ($normalized !== '') {
                return $normalized;
            }
        }
        return '';
    }

    // ========== CLIENT AREA ==========

    public function clientArea()
    {
        $serverId = $this->getServerId();
        $userId   = $this->getUserId();
        $_page    = in_array($this->page, ['home'], true) ? $this->page : 'home';
        $panelUrl = $this->getPanelUrl();

        $_data = [
            'url'          => $this->url,
            'lang'         => $this->lang,
            'area_link'    => $this->area_link,
            'panel_url'    => $panelUrl,
            'server_id'    => $serverId,
            'user_id'      => $userId,
            'status'       => 'N/A',
            'hostname'     => $this->options['hostname'] ?? 'N/A',
            'ip'           => $this->options['ip'] ?? 'N/A',
            'ipv6'         => '',
            'os'           => 'N/A',
            'cpu'          => 'N/A',
            'ram'          => 'N/A',
            'disk'         => 'N/A',
            'bandwidth'    => 'N/A',
            'location'     => 'N/A',
            'templates'    => [],
            'package_id'   => 0,
            'has_hostname' => false,
        ];

        if ($serverId) {
            $server = $this->api->getServer($serverId, true);
            if ($server && isset($server['data'])) {
                $sd = $server['data'];

                $remoteStateRaw = $sd['remoteState'] ?? null;
                $remoteState    = null;
                if (is_bool($remoteStateRaw)) {
                    $remoteState = $remoteStateRaw;
                } elseif ($remoteStateRaw === 1 || $remoteStateRaw === '1' || $remoteStateRaw === 'true') {
                    $remoteState = true;
                } elseif ($remoteStateRaw === 0 || $remoteStateRaw === '0' || $remoteStateRaw === 'false') {
                    $remoteState = false;
                }

                $state      = strtolower((string)($sd['state'] ?? 'unknown'));
                $runStates  = ['running', 'active', 'online', 'started', 'restarting', 'rebooting', 'booting', 'up'];
                $stopStates = ['stopped', 'shutoff', 'shutdown', 'offline', 'poweroff', 'halted', 'down'];
                $provStates = ['allocated', 'allocating', 'building', 'creating', 'installing', 'reinstalling', 'pending', 'provisioning'];

                $apiHostname           = $this->extractApiHostname($sd);
                $hasHostname           = ($apiHostname !== '');
                $_data['has_hostname'] = $hasHostname;

                if (!$hasHostname) {
                    $_data['status'] = 'Pending User Setup';
                } elseif ($remoteState === true) {
                    $_data['status'] = 'Online';
                } elseif ($remoteState === false) {
                    $_data['status'] = 'Offline';
                } elseif (in_array($state, $runStates, true)) {
                    $_data['status'] = 'Online';
                } elseif (in_array($state, $stopStates, true)) {
                    $_data['status'] = 'Offline';
                } elseif (in_array($state, $provStates, true)) {
                    $_data['status'] = 'Online';
                } else {
                    $_data['status'] = $this->mapServerStatus($state);
                }
                $_data['status_raw']   = $state;
                $_data['remote_state'] = $remoteState;

                $storedHostname    = $this->normalizeHostname($this->options['hostname'] ?? '');
                $_data['hostname'] = $hasHostname ? $apiHostname : ($storedHostname !== '' ? $storedHostname : 'N/A');

                // IPv4
                $ipv4 = null;
                if (isset($sd['network']['interfaces']) && is_array($sd['network']['interfaces'])) {
                    foreach ($sd['network']['interfaces'] as $iface) {
                        if (isset($iface['ipv4']) && is_array($iface['ipv4'])) {
                            foreach ($iface['ipv4'] as $ip) {
                                if (!empty($ip['address'])) {
                                    $ipv4 = $ip['address'];
                                    break 2;
                                }
                            }
                        }
                    }
                }
                if (!$ipv4 && isset($sd['network']['ipv4'][0]['address'])) {
                    $ipv4 = $sd['network']['ipv4'][0]['address'];
                }
                if ($ipv4) {
                    $_data['ip'] = $ipv4;
                }

                // IPv6
                $ipv6 = null;
                if (isset($sd['network']['interfaces']) && is_array($sd['network']['interfaces'])) {
                    foreach ($sd['network']['interfaces'] as $iface) {
                        if (isset($iface['ipv6']) && is_array($iface['ipv6'])) {
                            foreach ($iface['ipv6'] as $ip) {
                                if (!empty($ip['address'])) {
                                    $ipv6 = $ip['address'];
                                    break 2;
                                }
                                if (!empty($ip['subnet'])) {
                                    $subnet = $ip['subnet'];
                                    $cidr   = $ip['cidr'] ?? 64;
                                    $ipv6   = rtrim($subnet, ':') . '/' . $cidr;
                                    break 2;
                                }
                            }
                        }
                    }
                }
                if ($ipv6) {
                    $_data['ipv6'] = $ipv6;
                }

                // OS Template
                $os = $sd['os']['templateName'] ?? $sd['template']['name'] ?? null;
                if ($os) {
                    $_data['os'] = $os;
                }

                // CPU
                $cpu = $sd['cpu']['cores'] ?? $sd['settings']['resources']['cpuCores'] ?? $sd['settings']['vcpu'] ?? null;
                if ($cpu) {
                    $_data['cpu'] = $cpu;
                }

                // RAM (MB)
                $memory = (int)($sd['settings']['resources']['memory'] ?? $sd['settings']['memory'] ?? 0);
                if ($memory > 0) {
                    $_data['ram'] = $this->formatBytes($memory * 1024 * 1024);
                }

                // Disk (GB)
                $storage = (int)($sd['settings']['resources']['storage'] ?? $sd['settings']['storage'] ?? 0);
                if ($storage > 0) {
                    $_data['disk'] = $this->formatBytes($storage * 1024 * 1024 * 1024);
                }

                // Bandwidth / Traffic (GB)
                $traffic = (int)($sd['settings']['resources']['traffic'] ?? $sd['settings']['traffic'] ?? 0);
                if ($traffic > 0) {
                    $_data['bandwidth'] = $this->formatBytes($traffic * 1024 * 1024 * 1024);
                }

                // Location
                $location = $sd['hypervisor']['group']['name'] ?? $sd['hypervisorGroup']['name'] ?? null;
                if ($location) {
                    $_data['location'] = $location;
                }

                // Hypervisor / NAT host IP
                $hypervisorIp = $sd['hypervisor']['ip'] ?? $sd['vnc']['ip'] ?? null;
                if ($hypervisorIp) {
                    $_data['dedicated_ip'] = $hypervisorIp;
                }

                $_data['package_id']  = $sd['packageId'] ?? $sd['resourcePackId'] ?? 0;
                $_data['vnc_enabled'] = $sd['vnc']['enabled'] ?? false;
                $_data['vnc_ip']      = $sd['vnc']['ip'] ?? '';
                $_data['vnc_port']    = $sd['vnc']['port'] ?? 0;
                $_data['uuid']        = $sd['uuid'] ?? null;
            }
        }

        if ($_page === 'home') {
            $this->page       = 'dashboard';
            $_data['buttons'] = $this->clientArea_buttons_output();
        }

        return $this->get_page('clientArea' . $_page, $_data);
    }

    private function formatBytes($bytes)
    {
        if ($bytes <= 0) {
            return 'N/A';
        }
        if ($bytes >= 1099511627776) {
            return round($bytes / 1099511627776, 2) . ' TB';
        }
        if ($bytes >= 1073741824) {
            return round($bytes / 1073741824, 2) . ' GB';
        }
        if ($bytes >= 1048576) {
            return round($bytes / 1048576, 2) . ' MB';
        }
        return $bytes . ' B';
    }

    public function clientArea_buttons()
    {
        $page    = ($this->page === 'dashboard') ? 'home' : $this->page;
        $buttons = [];

        if ($page && $page !== 'home') {
            $buttons['home'] = ['text' => $this->lang['turn-back'] ?? 'Back', 'type' => 'page-loader'];
            return $buttons;
        }

        $serverId = $this->getServerId();

        if ($serverId) {
            $buttons['start'] = [
                'text' => $this->lang['button_start'] ?? 'Start',
                'type' => 'transaction',
                'icon' => 'fa fa-play-circle',
            ];
            $buttons['restart'] = [
                'text' => $this->lang['button_restart'] ?? 'Restart',
                'type' => 'transaction',
                'icon' => 'fa fa-refresh',
            ];
            $buttons['stop'] = [
                'text' => $this->lang['button_shutdown'] ?? 'Shutdown',
                'type' => 'transaction',
                'icon' => 'fa fa-stop-circle',
            ];
            $buttons['poweroff'] = [
                'text' => $this->lang['button_poweroff'] ?? 'Power Off',
                'type' => 'transaction',
                'icon' => 'fa fa-power-off',
            ];
            $buttons['resetpassword'] = [
                'text' => $this->lang['button_define_panel_password'] ?? 'Define Panel Password',
                'type' => 'transaction',
                'icon' => 'fa fa-key',
            ];
            $buttons['forgotpassword'] = [
                'text' => $this->lang['button_forgot_password'] ?? 'Forgot Password',
                'type' => 'transaction',
                'icon' => 'fa fa-unlock-alt',
            ];
        }

        return $buttons;
    }

    private function sendJson(array $payload, $noStore = false)
    {
        if (!headers_sent()) {
            header('Content-Type: application/json; charset=utf-8');
            if ($noStore) {
                header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
                header('Pragma: no-cache');
            }
        }
        echo json_encode($payload);
    }

    // SSO - Redirect to VirtFusion control panel
    public function use_clientArea_SingleSignOn()
    {
        $panelUrl = $this->getPanelUrl();
        $serverId = $this->getServerId();
        $userId   = $this->getUserId();

        $ssoUrl = null;
        if ($userId && $serverId) {
            $result = $this->api->createServerAuthTokenById($userId, $serverId);
            if ($result && isset($result['data']['authentication']['endpoint_complete'])) {
                $endpointComplete = trim((string)$result['data']['authentication']['endpoint_complete']);
                if (strpos($endpointComplete, '/') === 0) {
                    $ssoUrl = rtrim($panelUrl, '/') . $endpointComplete;
                }
            }
        }

        $finalUrl = str_replace(["\r", "\n"], '', $ssoUrl ?: $panelUrl);
        if (!preg_match('#^https?://#i', $finalUrl)) {
            $finalUrl = $panelUrl;
        }

        if (!headers_sent()) {
            header('Cache-Control: no-store, no-cache, must-revalidate, max-age=0');
            header('Location: ' . $finalUrl);
            exit;
        }

        $jsUrl   = json_encode($finalUrl, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
        $htmlUrl = htmlspecialchars($finalUrl, ENT_QUOTES, 'UTF-8');
        echo '<script>window.location.href=' . $jsUrl . ';</script>';
        echo '<meta http-equiv="refresh" content="0;url=' . $htmlUrl . '">';
        exit;
    }

    // Power actions
    public function use_clientArea_start()
    {
        $serverId = $this->getServerId();
        if (!$serverId) {
            $this->sendJson(['status' => 'error', 'message' => 'Server not found']);
            return false;
        }
        $result = $this->api->startServer($serverId);
        if ($result === false) {
            $this->sendJson(['status' => 'error', 'message' => $this->api->error]);
            return false;
        }
        $this->sendJson(['status' => 'successful', 'message' => 'Starting...']);
        return true;
    }

    public function use_clientArea_stop()
    {
        $serverId = $this->getServerId();
        if (!$serverId) {
            $this->sendJson(['status' => 'error', 'message' => 'Server not found']);
            return false;
        }
        $result = $this->api->stopServer($serverId);
        if ($result === false) {
            $this->sendJson(['status' => 'error', 'message' => $this->api->error]);
            return false;
        }
        $this->sendJson(['status' => 'successful', 'message' => 'Stopping...']);
        return true;
    }

    public function use_clientArea_restart()
    {
        $serverId = $this->getServerId();
        if (!$serverId) {
            $this->sendJson(['status' => 'error', 'message' => 'Server not found']);
            return false;
        }
        $result = $this->api->restartServer($serverId);
        if ($result === false) {
            $this->sendJson(['status' => 'error', 'message' => $this->api->error]);
            return false;
        }
        $this->sendJson(['status' => 'successful', 'message' => 'Restarting...']);
        return true;
    }

    public function use_clientArea_poweroff()
    {
        $serverId = $this->getServerId();
        if (!$serverId) {
            $this->sendJson(['status' => 'error', 'message' => 'Server not found']);
            return false;
        }
        $result = $this->api->poweroffServer($serverId);
        if ($result === false) {
            $this->sendJson(['status' => 'error', 'message' => $this->api->error]);
            return false;
        }
        $this->sendJson(['status' => 'successful', 'message' => $this->lang['poweroff_success'] ?? 'Powering off...']);
        return true;
    }

    // Define Panel Password - resets VirtFusion account password (not VPS root password)
    public function use_clientArea_resetpassword()
    {
        $clientId = (int)($this->order['owner_id'] ?? $this->order['client_id'] ?? $this->getClientData()['client_id'] ?? 0);

        if ($clientId <= 0) {
            $this->sendJson(['status' => 'error', 'message' => 'Client not found'], true);
            return false;
        }

        $result = $this->api->resetUserPasswordByExtRelation($clientId);

        if ($result === false || !isset($result['data'])) {
            $this->sendJson(['status' => 'error', 'message' => $this->api->error ?: 'Failed to reset password'], true);
            return false;
        }

        $email    = $result['data']['email'] ?? '';
        $password = $result['data']['password'] ?? '';

        $msg  = $this->lang['panel_password_success'] ?? 'Panel password defined!';
        $msg .= "\n\nEmail: " . $email . "\nPassword: " . $password;
        $msg .= "\n\n" . ($this->lang['panel_password_hint'] ?? 'Use these credentials to login to the Panel.');

        $this->sendJson(['status' => 'successful', 'message' => $msg], true);
        return true;
    }

    // Forgot Password - redirect to VirtFusion forgot password page
    public function use_clientArea_forgotpassword()
    {
        $panelUrl  = $this->getPanelUrl();
        $forgotUrl = rtrim($panelUrl, '/') . '/forgot-password';

        $this->sendJson([
            'status'   => 'successful',
            'message'  => $this->lang['forgot_password_redirect'] ?? 'Redirecting to password recovery...',
            'redirect' => $forgotUrl,
        ]);
        return true;
    }

    private function generatePassword($length = 16)
    {
        return VirtFusionAPI::generateSecurePassword($length);
    }

    // Admin mirrors
    public function adminArea_buttons() { return $this->clientArea_buttons(); }
    public function use_adminArea_SingleSignOn() { return $this->use_clientArea_SingleSignOn(); }
    public function use_adminArea_start() { return $this->use_clientArea_start(); }
    public function use_adminArea_stop() { return $this->use_clientArea_stop(); }
    public function use_adminArea_restart() { return $this->use_clientArea_restart(); }
    public function use_adminArea_poweroff() { return $this->use_clientArea_poweroff(); }
    public function use_adminArea_resetpassword() { return $this->use_clientArea_resetpassword(); }
    public function use_adminArea_forgotpassword() { return $this->use_clientArea_forgotpassword(); }
}
