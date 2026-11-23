# Changelog

All notable changes to this project will be documented in this file.

## [1.0.0] - 2026-11-22

### Initial Open-Source Release
- Complete VirtFusion v1 API client (`api.php`) and WiseCP `ServerModule` implementation (`VirtFusion.php`)
- Automated VPS provisioning (`create`), `suspend`, `unsuspend`, and `terminate`
- Client area dashboard (`pages/clientAreahome.php`) with status badge, network/resource/system cards, power controls, SSO panel login, VNC console link, and panel password management
- Admin order management view (`pages/order-detail.php`) and product configuration form (`pages/create-form-elements.php`)
- Multi-language support across 9 locales (`en`, `pt`, `pt-PT`, `fr`, `es`, `de`, `ar`, `zh-CN`, `chinese`)
- Security hardening: TLS verification enabled by default, automatic log redaction for passwords and SSO tokens, cryptographically secure password generation, escaped HTML/JS outputs, and strict API parameter encoding
