<?php
$e = static function ($value) {
    return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8');
};

$statusLower     = strtolower(str_replace(['/', ' ', '-'], '', (string)($status ?? 'unknown')));
$showStatusBadge = ($statusLower === 'pendingusersetup' && empty($has_hostname));
$statusBadgeText = $lang['pending_user_setup'] ?? 'Pending User Setup';

$ipv4Display = 'N/A';
$ipv4Type    = '';
if (!empty($ip) && $ip !== 'N/A') {
    if (preg_match('/^10\./', $ip) || preg_match('/^172\.(1[6-9]|2[0-9]|3[0-1])\./', $ip) || preg_match('/^192\.168\./', $ip)) {
        $ipv4Display = 'NAT - ' . $ip;
        $ipv4Type    = 'nat';
    } else {
        $ipv4Display = $ip;
        $ipv4Type    = 'public';
    }
}
$ipv6Display = (!empty($ipv6) && $ipv6 !== 'N/A') ? $ipv6 : '';
$dedicatedIp = $dedicated_ip ?? '';
$areaLink    = (string)($area_link ?? '');
$panelUrl    = rtrim((string)($panel_url ?? ''), '/');
$urlSep      = (strpos($areaLink, '?') !== false) ? '&' : '?';
?>
<style type="text/css">
.module-content-area,.serverserviceblock,.serverservicecontent,.servicecontent{
    width:100%!important;
    max-width:100%!important;
    margin:0 auto!important;
    float:none!important;
    flex:0 0 100%!important
}

.vf-wrap{font-family:-apple-system,BlinkMacSystemFont,'Segoe UI',Roboto,sans-serif;width:100%;max-width:100%;margin:0 auto;padding:0 15px}
.vf-head{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);border-radius:12px;padding:20px 24px;margin-bottom:20px;display:flex;justify-content:space-between;align-items:flex-start;flex-wrap:wrap;gap:16px}
.vf-head-left h2{color:#fff;font-size:20px;font-weight:700;margin:0 0 6px 0;text-align:left}
.vf-head-left p{color:rgba(255,255,255,.8);font-size:13px;margin:0;text-align:left}
.vf-badge{display:inline-flex;align-items:center;gap:6px;padding:8px 16px;border-radius:20px;font-weight:600;font-size:12px;text-transform:uppercase;background:rgba(255,255,255,.2);color:#fff;border:1px solid rgba(255,255,255,.2)}
.vf-badge.running,.vf-badge.active,.vf-badge.online,.vf-badge.started{background:rgba(16,185,129,.3);border-color:#10b981}
.vf-badge.stopped,.vf-badge.offline,.vf-badge.shutoff{background:rgba(239,68,68,.3);border-color:#ef4444}
.vf-badge.suspended,.vf-badge.unknown,.vf-badge.na,.vf-badge.provisioning{background:rgba(245,158,11,.3);border-color:#f59e0b}
.vf-dot{width:8px;height:8px;border-radius:50%;background:#fff;animation:vfpulse 2s infinite}
@keyframes vfpulse{0%,100%{opacity:1}50%{opacity:.4}}
.vf-btns{display:grid;grid-template-columns:repeat(auto-fit,minmax(180px,1fr));gap:10px;margin-bottom:20px}
.vf-btn{display:flex;align-items:center;justify-content:center;gap:8px;padding:14px 12px;border-radius:8px;font-weight:600;font-size:12px;text-decoration:none!important;cursor:pointer;transition:all .2s;border:1px solid #e2e8f0;background:#fff;color:#334155!important;text-align:center}
.vf-btn:hover{transform:translateY(-1px);box-shadow:0 4px 12px rgba(0,0,0,.1)}
.vf-btn i{font-size:14px}
.vf-btn.primary{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);border:none;color:#fff!important}
.vf-btn.green{border-color:#10b981;color:#10b981!important}
.vf-btn.green:hover{background:#10b981;color:#fff!important}
.vf-btn.red{border-color:#ef4444;color:#ef4444!important}
.vf-btn.red:hover{background:#ef4444;color:#fff!important}
.vf-btn.orange{border-color:#f59e0b;color:#f59e0b!important}
.vf-btn.orange:hover{background:#f59e0b;color:#fff!important}
.vf-btn.purple{border-color:#8b5cf6;color:#8b5cf6!important}
.vf-btn.purple:hover{background:#8b5cf6;color:#fff!important}
.vf-btn.blue{border-color:#3b82f6;color:#3b82f6!important}
.vf-btn.blue:hover{background:#3b82f6;color:#fff!important}
.vf-cards{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:16px;margin-bottom:20px}
.vf-card{background:#fff;border:1px solid #e2e8f0;border-radius:12px;overflow:hidden}
.vf-card-head{background:#f8fafc;padding:14px 18px;border-bottom:1px solid #e2e8f0;display:flex;align-items:center;gap:10px}
.vf-card-head i{width:32px;height:32px;background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);border-radius:8px;display:flex;align-items:center;justify-content:center;color:#fff;font-size:14px}
.vf-card-head span{font-weight:600;font-size:14px;color:#1e293b}
.vf-card-body{padding:16px 18px}
.vf-row{display:flex;justify-content:space-between;align-items:center;padding:10px 0;border-bottom:1px dashed #e2e8f0}
.vf-row:last-child{border-bottom:none}
.vf-row-label{display:flex;align-items:center;gap:8px;color:#64748b;font-size:13px}
.vf-row-value{font-weight:600;color:#1e293b;font-size:13px;word-break:break-all;text-align:right;max-width:60%}
.vf-row-value.mono{font-family:monospace;font-size:12px}
.vf-row-value.nat{color:#f59e0b}
.vf-row-value.na{color:#94a3b8}
.vf-row-value.dedicated{color:#667eea;font-weight:700}
@media (max-width: 1200px){
    .vf-wrap{padding:0 10px}
}
@media (max-width: 768px){
    .vf-head{padding:16px}
    .vf-btns{grid-template-columns:1fr}
}
</style>

<div class="vf-wrap">
    <div class="vf-head">
        <div class="vf-head-left">
            <h2><?php echo $e($hostname ?? 'Virtual Server'); ?></h2>
            <p>
                <i class="fa fa-map-marker"></i> <?php echo $e($location ?? 'N/A'); ?> |
                <i class="fa fa-globe"></i> <?php echo $e($ipv4Type === 'public' ? $ip : ($ipv6Display ?: ($ip ?? 'N/A'))); ?>
            </p>
        </div>
        <?php if ($showStatusBadge): ?>
        <div class="vf-badge provisioning" id="vf-status-badge">
            <?php echo $e($statusBadgeText); ?>
        </div>
        <?php endif; ?>
    </div>

    <div class="vf-btns">
        <!-- Control Panel -->
        <a href="<?php echo $e($areaLink . $urlSep . 'inc=panel_operation_method&method=SingleSignOn'); ?>" target="_blank" rel="noopener noreferrer" class="vf-btn primary">
            <i class="fa fa-sign-in"></i> <?php echo $e($lang['button_panel'] ?? 'Panel'); ?>
        </a>

        <?php if (!empty($server_id)): ?>
            <!-- Power controls -->
            <a href="javascript:void(0);" onclick="vfAction('start',this)" class="vf-btn green">
                <i class="fa fa-play"></i> <?php echo $e($lang['button_start'] ?? 'Start'); ?>
            </a>
            <a href="javascript:void(0);" onclick="vfAction('restart',this)" class="vf-btn orange">
                <i class="fa fa-refresh"></i> <?php echo $e($lang['button_restart'] ?? 'Restart'); ?>
            </a>
            <a href="javascript:void(0);" onclick="vfAction('stop',this)" class="vf-btn red">
                <i class="fa fa-stop"></i> <?php echo $e($lang['button_shutdown'] ?? 'Shutdown'); ?>
            </a>
            <a href="javascript:void(0);" onclick="vfAction('poweroff',this)" class="vf-btn red">
                <i class="fa fa-power-off"></i> <?php echo $e($lang['button_poweroff'] ?? 'Power Off'); ?>
            </a>

            <!-- Define Panel Password -->
            <a href="javascript:void(0);" onclick="vfDefinePanelPw()" class="vf-btn purple">
                <i class="fa fa-key"></i> <?php echo $e($lang['button_define_panel_password'] ?? 'Define Panel Password'); ?>
            </a>

            <!-- VNC Console -->
            <?php if (!empty($uuid) && !empty($panelUrl)): ?>
            <a href="<?php echo $e($panelUrl . '/server/' . rawurlencode((string)$uuid) . '/vnc'); ?>" target="_blank" rel="noopener noreferrer" class="vf-btn purple">
                <i class="fa fa-desktop"></i> <?php echo $e($lang['button_vnc'] ?? 'VNC'); ?>
            </a>
            <?php else: ?>
            <a class="vf-btn" style="opacity:0.5;pointer-events:none;">
                <i class="fa fa-desktop"></i> <?php echo $e($lang['button_vnc'] ?? 'VNC'); ?>
            </a>
            <?php endif; ?>

            <!-- Forgot Password on Panel -->
            <?php if (!empty($panelUrl)): ?>
            <a href="<?php echo $e($panelUrl . '/forgot-password'); ?>" target="_blank" rel="noopener noreferrer" class="vf-btn blue">
                <i class="fa fa-unlock-alt"></i> <?php echo $e($lang['button_forgot_password'] ?? 'Forgot Password'); ?>
            </a>
            <?php endif; ?>
        <?php endif; ?>
    </div>

    <div class="vf-cards">
        <div class="vf-card">
            <div class="vf-card-head"><i class="fa fa-sitemap"></i><span><?php echo $e($lang['network'] ?? 'Network'); ?></span></div>
            <div class="vf-card-body">
                <div class="vf-row">
                    <span class="vf-row-label"><i class="fa fa-globe"></i> IPv4</span>
                    <span class="vf-row-value mono <?php echo $ipv4Type === 'nat' ? 'nat' : ($ipv4Display === 'N/A' ? 'na' : ''); ?>"><?php echo $e($ipv4Display); ?></span>
                </div>
                <div class="vf-row">
                    <span class="vf-row-label"><i class="fa fa-globe"></i> IPv6</span>
                    <span class="vf-row-value mono <?php echo empty($ipv6Display) ? 'na' : ''; ?>" style="<?php echo $ipv6Display ? 'font-size:10px' : ''; ?>"><?php echo $e($ipv6Display ?: 'N/A'); ?></span>
                </div>
                <div class="vf-row">
                    <span class="vf-row-label"><i class="fa fa-exchange"></i> <?php echo $e($lang['bandwidth'] ?? 'Traffic'); ?></span>
                    <span class="vf-row-value"><?php echo $e($bandwidth ?? 'N/A'); ?></span>
                </div>
                <?php if ($dedicatedIp): ?>
                <div class="vf-row">
                    <span class="vf-row-label"><i class="fa fa-server"></i> <?php echo $e($lang['dedicated_ip'] ?? 'Server IP'); ?></span>
                    <span class="vf-row-value mono dedicated"><?php echo $e($dedicatedIp); ?></span>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <div class="vf-card">
            <div class="vf-card-head"><i class="fa fa-microchip"></i><span><?php echo $e($lang['resources'] ?? 'Resources'); ?></span></div>
            <div class="vf-card-body">
                <div class="vf-row">
                    <span class="vf-row-label"><i class="fa fa-microchip"></i> <?php echo $e($lang['cpu'] ?? 'CPU'); ?></span>
                    <span class="vf-row-value"><?php echo ($cpu ?? 'N/A') !== 'N/A' ? $e($cpu) . ' vCPU' : 'N/A'; ?></span>
                </div>
                <div class="vf-row">
                    <span class="vf-row-label"><i class="fa fa-database"></i> <?php echo $e($lang['ram'] ?? 'RAM'); ?></span>
                    <span class="vf-row-value"><?php echo $e($ram ?? 'N/A'); ?></span>
                </div>
                <div class="vf-row">
                    <span class="vf-row-label"><i class="fa fa-hdd-o"></i> <?php echo $e($lang['disk'] ?? 'Disk'); ?></span>
                    <span class="vf-row-value"><?php echo $e($disk ?? 'N/A'); ?></span>
                </div>
            </div>
        </div>

        <div class="vf-card">
            <div class="vf-card-head"><i class="fa fa-server"></i><span><?php echo $e($lang['system'] ?? 'System'); ?></span></div>
            <div class="vf-card-body">
                <div class="vf-row">
                    <span class="vf-row-label"><i class="fa fa-linux"></i> <?php echo $e($lang['os'] ?? 'OS'); ?></span>
                    <span class="vf-row-value"><?php echo $e($os ?? 'N/A'); ?></span>
                </div>
                <div class="vf-row">
                    <span class="vf-row-label"><i class="fa fa-tag"></i> <?php echo $e($lang['hostname'] ?? 'Hostname'); ?></span>
                    <span class="vf-row-value"><?php echo $e($hostname ?? 'N/A'); ?></span>
                </div>
                <div class="vf-row">
                    <span class="vf-row-label"><i class="fa fa-map-marker"></i> <?php echo $e($lang['location'] ?? 'Location'); ?></span>
                    <span class="vf-row-value"><?php echo $e($location ?? 'N/A'); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
var vfActionBaseUrl = <?php echo json_encode($areaLink . $urlSep . 'inc=panel_operation_method&method=', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
var vfLang = {
    confirmAction: <?php echo json_encode($lang['confirm_action'] ?? 'Are you sure?', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>,
    confirmDefinePanelPw: <?php echo json_encode($lang['confirm_define_panel_password'] ?? 'Define Panel password? If you defined it previously, or if this is your 1st access, this generates a new Panel password. Use the email and password on the Panel option.', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>,
    pendingUserSetup: <?php echo json_encode($statusBadgeText, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>
};
var vfInitialStatus = <?php echo json_encode($status ?? 'Provisioning', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
var vfInitialStatusLower = <?php echo json_encode($statusLower ?? 'provisioning', JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP); ?>;
var vfForceKey = 'vf_force_status_<?php echo (int)($server_id ?? 0); ?>';
var vfHasHostname = <?php echo !empty($has_hostname) ? 'true' : 'false'; ?>;

function setBadge(stateText) {
    var badge = document.getElementById('vf-status-badge');
    if (!badge) return;
    badge.textContent = stateText;
    badge.classList.remove('online', 'offline', 'provisioning');
    var low = (stateText || '').toLowerCase();
    if (low.includes('prov') || low.includes('pend')) badge.classList.add('provisioning');
    else if (low.includes('off')) badge.classList.add('offline');
    else badge.classList.add('online');
}

function vfAction(action, el) {
    if (!confirm(vfLang.confirmAction)) return;
    $(el).css('pointer-events', 'none').html('<i class="fa fa-spinner fa-spin"></i>');
    $.getJSON(vfActionBaseUrl + encodeURIComponent(action), function(r) {
        if (r && r.status === 'successful') {
            var delay = 15000;
            if (action === 'stop' || action === 'poweroff') delay = 40000;
            if (action === 'restart') delay = 20000;
            if (action === 'start' || action === 'restart') {
                setBadge('Online');
                localStorage.setItem(vfForceKey, JSON.stringify({status: 'Online'}));
            }
            if (action === 'stop' || action === 'poweroff') {
                setBadge('Offline');
                localStorage.setItem(vfForceKey, JSON.stringify({status: 'Offline'}));
            }
            setTimeout(function() { location.reload(); }, delay);
        } else {
            alert((r && r.message) ? r.message : 'Error');
            location.reload();
        }
    });
}

function vfDefinePanelPw() {
    if (!confirm(vfLang.confirmDefinePanelPw)) return;
    $.getJSON(vfActionBaseUrl + 'resetpassword', function(r) {
        alert((r && r.message) ? r.message : 'Done');
    });
}

(function() {
    try {
        var data = JSON.parse(localStorage.getItem(vfForceKey) || '{}');
        if (data.status) {
            if (vfInitialStatusLower.indexOf('pendingusersetup') >= 0) {
                localStorage.removeItem(vfForceKey);
                setBadge(vfHasHostname ? 'Provisioned' : vfLang.pendingUserSetup);
                return;
            }
            setBadge(data.status);
            return;
        }
    } catch (e) {}
    if (vfInitialStatusLower.indexOf('pendingusersetup') >= 0) {
        localStorage.removeItem(vfForceKey);
        setBadge(vfHasHostname ? 'Provisioned' : vfLang.pendingUserSetup);
    } else {
        setBadge(vfInitialStatus || 'Online');
    }
})();
</script>
