<?php
$options       = $order['options'] ?? [];
$creation_info = $options['creation_info'] ?? [];
$config        = $options['config'] ?? [];
$buttons       = $module->adminArea_buttons_output();
?>

<?php if ($buttons): ?>
<div class="formcon"><?php echo $buttons; ?></div>
<div class="clear"></div>
<?php endif; ?>

<div class="formcon">
    <div class="yuzde30">Server ID</div>
    <div class="yuzde70"><input class="yuzde10" type="text" name="config[server_id]" value="<?php echo htmlspecialchars((string)($config['server_id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"></div>
</div>

<div class="formcon">
    <div class="yuzde30">User ID</div>
    <div class="yuzde70"><input class="yuzde10" type="text" name="config[user_id]" value="<?php echo htmlspecialchars((string)($config['user_id'] ?? ''), ENT_QUOTES, 'UTF-8'); ?>"></div>
</div>

<?php
if (method_exists($module, 'config_options') && $config_options = $module->config_options($creation_info)) {
    $module->config_options_output($config_options, 'creation_info');
}
?>
