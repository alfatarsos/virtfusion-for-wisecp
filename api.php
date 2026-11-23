<?php
/**
 * VirtFusion API SDK for WiseCP
 *
 * @author    Tiago Pereira
 * @copyright Copyright (c) 2026 Tiago Pereira
 * @version   1.0.0
 * @link      https://docs.virtfusion.com/api/
 * @license   MIT
 */
class VirtFusionAPI
{
    public $hostname;
    public $port;
    public $ssl;
    public $api_token;
    public $verify_ssl = true;
    public $status_code;
    public $error = '';

    /**
     * Sensitive keys whose values are redacted before writing to WiseCP module logs.
     */
    private const SENSITIVE_KEY_PATTERN = '/(pass(word|wd)?|token|secret|access_hash|authorization|endpoint_complete|authentication|api_key)/i';

    public function __construct($hostname, $port = 443, $ssl = true, $api_token = '', $verify_ssl = true)
    {
        $this->hostname   = trim((string)$hostname);
        $this->port       = (int)$port;
        $this->ssl        = !($ssl === false || $ssl === 0 || $ssl === '0');
        $this->api_token  = (string)$api_token;
        $this->verify_ssl = (bool)$verify_ssl;
    }

    public function getBaseUrl()
    {
        $host = $this->hostname;
        if (stripos($host, 'http://') === 0 || stripos($host, 'https://') === 0) {
            return rtrim($host, '/');
        }

        $scheme = $this->ssl ? 'https' : 'http';
        $host   = rtrim($host, '/');

        if (
            $this->port > 0
            && $this->port <= 65535
            && strpos($host, ':') === false
            && !(($scheme === 'https' && $this->port === 443) || ($scheme === 'http' && $this->port === 80))
        ) {
            return $scheme . '://' . $host . ':' . $this->port;
        }

        return $scheme . '://' . $host;
    }

    /**
     * Recursively redact secrets, passwords, and SSO tokens before logging.
     *
     * @param mixed $value
     * @return mixed
     */
    public static function sanitizeLogData($value)
    {
        if (is_array($value)) {
            $clean = [];
            foreach ($value as $k => $v) {
                if (is_string($k) && preg_match(self::SENSITIVE_KEY_PATTERN, $k)) {
                    $clean[$k] = '[REDACTED]';
                } else {
                    $clean[$k] = self::sanitizeLogData($v);
                }
            }
            return $clean;
        }
        return $value;
    }

    /**
     * Generate a cryptographically secure random password.
     */
    public static function generateSecurePassword($length = 16)
    {
        $chars    = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789';
        $maxIndex = strlen($chars) - 1;
        $password = '';
        for ($i = 0; $i < $length; $i++) {
            $password .= $chars[random_int(0, $maxIndex)];
        }
        return $password;
    }

    public function call($method = 'GET', $endpoint = '', $params = [], $data = [])
    {
        $this->error = '';
        $method      = strtoupper((string)$method);
        $endpoint    = ltrim((string)$endpoint, '/');
        $url         = $this->getBaseUrl() . '/api/v1/' . $endpoint;

        if ($params && is_array($params) && $method === 'GET') {
            $url .= (strpos($url, '?') === false ? '?' : '&') . http_build_query($params);
        }

        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL            => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CONNECTTIMEOUT => 15,
            CURLOPT_TIMEOUT        => 60,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_SSL_VERIFYHOST => $this->verify_ssl ? 2 : 0,
            CURLOPT_SSL_VERIFYPEER => $this->verify_ssl,
            CURLOPT_CUSTOMREQUEST  => $method,
            CURLOPT_HTTPHEADER     => [
                'Authorization: Bearer ' . $this->api_token,
                'Accept: application/json',
                'Content-Type: application/json',
            ],
        ]);

        if (in_array($method, ['POST', 'PUT', 'PATCH'], true)) {
            $payload = !empty($data) ? $data : $params;
            if (!empty($payload)) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($payload));
            }
        }

        $response          = curl_exec($curl);
        $error             = curl_errno($curl) ? curl_error($curl) : null;
        $httpCode          = (int)curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $this->status_code = $httpCode;
        curl_close($curl);

        if ($error) {
            $this->error = $error;
            if (class_exists('Modules')) {
                Modules::save_log(
                    'Servers',
                    'VirtFusion',
                    $method . ' ' . $endpoint . ' [CURL_ERROR]',
                    ['body' => self::sanitizeLogData($data)],
                    $error
                );
            }
            return false;
        }

        $decoded = json_decode((string)$response, true);

        // Log only mutating calls or HTTP errors, always with sensitive fields redacted.
        if (class_exists('Modules') && ($method !== 'GET' || $httpCode >= 400)) {
            $safeResponse = is_array($decoded)
                ? json_encode(self::sanitizeLogData($decoded))
                : ($httpCode >= 400 ? substr((string)$response, 0, 500) : '[NON-JSON RESPONSE]');
            Modules::save_log(
                'Servers',
                'VirtFusion',
                $method . ' ' . $endpoint . ' [HTTP:' . $httpCode . ']',
                ['body' => self::sanitizeLogData($data), 'http' => $httpCode],
                $safeResponse
            );
        }

        if ($decoded === null && !empty($response)) {
            $this->error = 'Invalid JSON response from VirtFusion API (HTTP ' . $httpCode . ')';
            return false;
        }

        if ($httpCode >= 400) {
            $this->error = 'HTTP Error ' . $httpCode;
            if (isset($decoded['message']) && is_string($decoded['message'])) {
                $this->error = $decoded['message'];
            }
            if (isset($decoded['errors']) && is_array($decoded['errors'])) {
                $errs = [];
                foreach ($decoded['errors'] as $f => $m) {
                    $errs[] = $f . ': ' . (is_array($m) ? implode(', ', $m) : $m);
                }
                $this->error .= ' - ' . implode('; ', $errs);
            }
            return false;
        }

        return $decoded;
    }

    // ========== PACKAGES ==========

    public function getPackages()
    {
        return $this->call('GET', 'packages', ['per_page' => 100]);
    }

    public function getPackage($packageId)
    {
        return $this->call('GET', 'packages/' . (int)$packageId);
    }

    /**
     * Get hypervisor groups available for a specific package.
     */
    public function getHypervisorGroupsForPackage($packageId)
    {
        $packageId = (int)$packageId;
        $pkg       = $this->getPackage($packageId);

        if ($pkg && isset($pkg['data']['hypervisorGroups']) && is_array($pkg['data']['hypervisorGroups'])) {
            return ['data' => $pkg['data']['hypervisorGroups']];
        }

        if ($pkg && isset($pkg['data']['locations']) && is_array($pkg['data']['locations'])) {
            return ['data' => $pkg['data']['locations']];
        }

        $result = $this->call('GET', 'packages/' . $packageId . '/hypervisorGroups');
        if ($result && isset($result['data'])) {
            return $result;
        }

        return $this->getHypervisorGroups();
    }

    // ========== HYPERVISOR GROUPS (Locations) ==========

    /**
     * Get all hypervisor groups - tries multiple API endpoints for version compatibility.
     *
     * @return array|false
     */
    public function getHypervisorGroups()
    {
        $result = $this->call('GET', 'compute/hypervisors/groups', ['per_page' => 100]);

        if (!$result || empty($result['data'])) {
            $result = $this->call('GET', 'hypervisors/groups', ['per_page' => 100]);
        }

        if (!$result || empty($result['data'])) {
            $result = $this->call('GET', 'compute/hypervisors/groups');
        }

        if (!$result || empty($result['data'])) {
            $result = $this->call('GET', 'locations', ['per_page' => 100]);
        }

        return $result;
    }

    public function getHypervisorGroup($hypervisorGroupId)
    {
        $id     = (int)$hypervisorGroupId;
        $result = $this->call('GET', 'compute/hypervisors/groups/' . $id);
        if (!$result || empty($result['data'])) {
            $result = $this->call('GET', 'hypervisors/groups/' . $id);
        }
        return $result;
    }

    public function getHypervisorGroupResources($hypervisorGroupId)
    {
        $id     = (int)$hypervisorGroupId;
        $result = $this->call('GET', 'compute/hypervisors/groups/' . $id . '/resources');
        if (!$result || empty($result['data'])) {
            $result = $this->call('GET', 'hypervisors/groups/' . $id . '/resources');
        }
        return $result;
    }

    public function validateHypervisorGroup($hypervisorGroupId)
    {
        $hypervisorGroupId = (int)$hypervisorGroupId;
        if ($hypervisorGroupId <= 0) {
            return false;
        }

        $result = $this->getHypervisorGroup($hypervisorGroupId);
        if ($result && isset($result['data']['id'])) {
            return true;
        }

        $allGroups = $this->getHypervisorGroups();
        if ($allGroups && isset($allGroups['data']) && is_array($allGroups['data'])) {
            foreach ($allGroups['data'] as $hg) {
                if (isset($hg['id']) && (int)$hg['id'] === $hypervisorGroupId) {
                    return true;
                }
            }
        }

        return false;
    }

    public function getFirstHypervisorGroupId()
    {
        $result = $this->getHypervisorGroups();
        if ($result && isset($result['data']) && is_array($result['data']) && count($result['data']) > 0) {
            return (int)($result['data'][0]['id'] ?? 0);
        }
        return 0;
    }

    // ========== TEMPLATES (Operating Systems) ==========

    public function getTemplatesByPackage($packageId)
    {
        $result = $this->call('GET', 'media/templates/fromServerPackageSpec/' . (int)$packageId);
        return $this->flattenTemplates($result);
    }

    public function getTemplatesByServer($serverId)
    {
        $result = $this->call('GET', 'servers/' . (int)$serverId . '/templates');
        return $this->flattenTemplates($result);
    }

    private function flattenTemplates($result)
    {
        if (!$result || !isset($result['data']) || !is_array($result['data'])) {
            return $result;
        }

        $flattened = [];
        foreach ($result['data'] as $group) {
            if (isset($group['templates']) && is_array($group['templates']) && count($group['templates']) > 0) {
                foreach ($group['templates'] as $tpl) {
                    $tplId = $tpl['id'] ?? null;
                    if ($tplId) {
                        $name = $tpl['name'] ?? $group['name'] ?? 'Unknown';
                        if (!empty($tpl['version'])) {
                            $name .= ' ' . $tpl['version'];
                        }
                        if (!empty($tpl['variant']) && $tpl['variant'] !== 'Minimal') {
                            $name .= ' - ' . $tpl['variant'];
                        }
                        $flattened[] = [
                            'id'   => $tplId,
                            'name' => trim($name),
                        ];
                    }
                }
            } elseif (isset($group['versions']) && is_array($group['versions']) && count($group['versions']) > 0) {
                foreach ($group['versions'] as $ver) {
                    $verId = $ver['id'] ?? null;
                    if ($verId) {
                        $name        = ($group['name'] ?? '') . ' ' . ($ver['version'] ?? $ver['name'] ?? '');
                        $flattened[] = [
                            'id'   => $verId,
                            'name' => trim($name),
                        ];
                    }
                }
            } elseif (isset($group['id']) && !isset($group['templates'])) {
                $flattened[] = [
                    'id'   => $group['id'],
                    'name' => $group['name'] ?? 'Unknown',
                ];
            }
        }

        $result['data'] = $flattened;
        return $result;
    }

    // ========== USERS ==========

    public function createUser($email, $name, $password, $extRelationId = null)
    {
        $data = [
            'email'    => $email,
            'name'     => $name,
            'password' => $password,
            'sendMail' => false,
        ];
        if ($extRelationId !== null && $extRelationId !== '') {
            $data['extRelationId'] = (string)$extRelationId;
        }
        return $this->call('POST', 'users', [], $data);
    }

    public function findUserIdByEmail($email)
    {
        $email   = strtolower(trim((string)$email));
        $servers = $this->call('GET', 'servers', ['per_page' => 200]);
        if ($servers && isset($servers['data']) && is_array($servers['data'])) {
            foreach ($servers['data'] as $srv) {
                $userEmail = strtolower(trim($srv['user']['email'] ?? ''));
                if ($userEmail === $email && isset($srv['user']['id'])) {
                    return (int)$srv['user']['id'];
                }
            }
        }
        return 0;
    }

    public function getOrCreateUser($email, $name, $password = null, $extRelationId = null)
    {
        $email = trim((string)$email);
        $name  = trim((string)$name);

        if (empty($email)) {
            $this->error = 'Email is required';
            return 0;
        }
        if (empty($name)) {
            $name = ucfirst(explode('@', $email)[0]);
        }
        if (empty($password)) {
            $password = self::generateSecurePassword(16);
        }

        if ($extRelationId) {
            $ext = $this->getUserByExtRelationId($extRelationId);
            if ($ext && isset($ext['data']['id'])) {
                return (int)$ext['data']['id'];
            }

            $this->error = '';
            $result      = $this->createUser($email, $name, $password, $extRelationId);
            if ($result && isset($result['data']['id'])) {
                return (int)$result['data']['id'];
            }

            if ($this->status_code === 409) {
                $ext = $this->getUserByExtRelationId($extRelationId);
                if ($ext && isset($ext['data']['id'])) {
                    return (int)$ext['data']['id'];
                }
            }

            $this->error = $this->error ?: 'Failed to resolve userId via extRelationId';
            return 0;
        }

        $this->error = '';
        $result      = $this->createUser($email, $name, $password, null);
        if ($result && isset($result['data']['id'])) {
            return (int)$result['data']['id'];
        }

        $this->error = $this->error ?: 'Failed to get/create user';
        return 0;
    }

    // ========== SSO ==========

    public function createUserAuthTokenById($userId)
    {
        return $this->call('POST', 'users/' . (int)$userId . '/authenticationTokens');
    }

    public function createServerAuthTokenById($userId, $serverId)
    {
        return $this->call('POST', 'users/' . (int)$userId . '/serverAuthenticationTokens/' . (int)$serverId);
    }

    public function createAuthTokenByExtRelationId($extRelationId)
    {
        return $this->call('POST', 'users/extRelationId:' . rawurlencode((string)$extRelationId) . '/authenticationTokens');
    }

    public function createServerAuthTokenByExtRelationId($extRelationId, $serverId)
    {
        return $this->call('POST', 'users/extRelationId:' . rawurlencode((string)$extRelationId) . '/serverAuthenticationTokens/' . (int)$serverId);
    }

    public function getUserByExtRelationId($extRelationId)
    {
        $encoded = rawurlencode((string)$extRelationId);
        $resp    = $this->call('GET', 'users/' . $encoded . '/byExtRelation');
        if ($resp !== false && isset($resp['data']['id'])) {
            return $resp;
        }
        return $this->call('GET', 'users/extRelationId:' . $encoded . '/byExtRelation');
    }

    // ========== SERVERS ==========

    public function createServer($data)
    {
        return $this->call('POST', 'servers', [], $data);
    }

    public function buildServer($serverId, $buildData = [])
    {
        if (!isset($buildData['enableVnc'])) {
            $buildData['enableVnc'] = true;
        }
        return $this->call('POST', 'servers/' . (int)$serverId . '/build', [], $buildData);
    }

    public function getServer($id, $includeRemoteState = true)
    {
        $params = [];
        if ($includeRemoteState) {
            $params['remoteState'] = 1;
        }
        return $this->call('GET', 'servers/' . (int)$id, $params);
    }

    public function getServers($params = [])
    {
        return $this->call('GET', 'servers', array_merge(['per_page' => 100], $params));
    }

    public function deleteServer($id)
    {
        return $this->call('DELETE', 'servers/' . (int)$id);
    }

    public function setServerIpv4Qty($serverId, $qty)
    {
        return $this->call('POST', 'servers/' . (int)$serverId . '/ipv4/' . (int)$qty);
    }

    // ========== POWER & LIFECYCLE ==========

    public function startServer($id)
    {
        return $this->call('POST', 'servers/' . (int)$id . '/power/boot');
    }

    public function stopServer($id)
    {
        return $this->call('POST', 'servers/' . (int)$id . '/power/shutdown');
    }

    public function restartServer($id)
    {
        return $this->call('POST', 'servers/' . (int)$id . '/power/restart');
    }

    public function poweroffServer($id)
    {
        return $this->call('POST', 'servers/' . (int)$id . '/power/poweroff');
    }

    public function suspendServer($id)
    {
        return $this->call('POST', 'servers/' . (int)$id . '/suspend');
    }

    public function unsuspendServer($id)
    {
        return $this->call('POST', 'servers/' . (int)$id . '/unsuspend');
    }

    // ========== TRAFFIC ==========

    public function getServerTraffic($id)
    {
        return $this->call('GET', 'servers/' . (int)$id . '/traffic');
    }

    public function getServerTrafficBlocks($id)
    {
        return $this->call('GET', 'servers/' . (int)$id . '/traffic/blocks');
    }

    // ========== VNC ==========

    public function enableVnc($serverId)
    {
        return $this->call('POST', 'servers/' . (int)$serverId . '/vnc');
    }

    public function getVnc($serverId)
    {
        return $this->call('GET', 'servers/' . (int)$serverId . '/vnc');
    }

    public function getVncUrl($serverId)
    {
        $serverId = (int)$serverId;
        $enable   = $this->enableVnc($serverId);
        if ($enable === false) {
            return null;
        }

        usleep(500000);

        $result = $this->getVnc($serverId);
        if ($result && isset($result['data'])) {
            return $result['data']['url'] ?? $result['data']['vncUrl'] ?? $result['data']['link'] ?? null;
        }
        return null;
    }

    // ========== REINSTALL & PASSWORDS ==========

    public function reinstallServer($serverId, $templateId, $password = null)
    {
        $data = ['templateId' => (int)$templateId];
        if ($password) {
            $data['password'] = $password;
        }
        return $this->call('POST', 'servers/' . (int)$serverId . '/build', [], $data);
    }

    public function resetServerPassword($serverId, $newPassword = null)
    {
        $data = [];
        if ($newPassword) {
            $data['password'] = $newPassword;
        }
        return $this->call('POST', 'servers/' . (int)$serverId . '/resetPassword', [], $data);
    }

    public function resetUserPasswordByExtRelation($clientId)
    {
        return $this->call('POST', 'users/' . rawurlencode((string)$clientId) . '/byExtRelation/resetPassword');
    }

    public function getUserByExtRelation($clientId)
    {
        return $this->call('GET', 'users/' . rawurlencode((string)$clientId) . '/byExtRelation');
    }

    public function changeServerPackage($serverId, $packageId)
    {
        return $this->call('PUT', 'servers/' . (int)$serverId . '/package/' . (int)$packageId);
    }

    public function getServerState($serverId)
    {
        $result = $this->call('GET', 'servers/' . (int)$serverId . '/state');
        if ($result && isset($result['data'])) {
            return $result['data'];
        }
        return null;
    }
}
