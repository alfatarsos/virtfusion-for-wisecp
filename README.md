# VirtFusion Server Module for WiseCP

An open-source **WiseCP** server provisioning module for **[VirtFusion](https://virtfusion.com/)** (KVM VPS virtualization control panel), built against the VirtFusion v1 API.

## Features

- **Automated Provisioning & Lifecycle Management**
  - Automatic VirtFusion user lookup/creation using WiseCP's client ID (`extRelationId`)
  - Automated VPS creation (`create`), `suspend`, `unsuspend`, and `terminate`
  - Configurable package, location (Hypervisor Group or VirtFusion auto-selection), default OS template, IPv4 quantity (including IPv6-only / NAT support), and IPv6 toggle
- **Client Area Dashboard**
  - Clean, responsive status and resource cards (IPv4 / NAT indicator, IPv6, traffic, hypervisor IP for NAT setups, CPU, RAM, disk, OS, hostname, location)
  - Power controls: **Start**, **Restart**, **Shutdown**, and **Force Power Off**
  - **Single Sign-On (SSO)** redirect directly into the VirtFusion control panel
  - **Direct VNC Console** button and **Define Panel Password** / **Forgot Password** helpers
- **Admin Area Controls**
  - Manual `server_id` and `user_id` overrides on orders
  - Mirrored power, SSO, and password actions for staff
- **Multi-language Support (9 locales included)**
  - English (`en`), Portuguese (`pt`, `pt-PT`), French (`fr`), Spanish (`es`), German (`de`), Arabic (`ar`), and Simplified Chinese (`zh-CN`, `chinese`)
- **Security Hardened**
  - Full TLS certificate verification (`CURLOPT_SSL_VERIFYPEER` / `CURLOPT_SSL_VERIFYHOST`) enabled by default
  - Automatic redaction of passwords, API tokens, and SSO authentication URLs before writing to WiseCP module logs
  - Cryptographically secure password generation (`random_int`)
  - Escaped client/admin HTML & JS outputs and strict API path parameter casting

## Requirements

- **WiseCP** 3.x+
- **PHP** 7.4 or 8.x with `curl` and `json` extensions
- **VirtFusion** Control Panel with API v1 access and a valid TLS certificate

## Installation

1. Download or clone this repository.
2. Copy the `VirtFusion/` directory into your WiseCP installation under:
   ```text
   coremio/modules/Servers/VirtFusion/
   ```
3. Verify the resulting structure:
   ```text
   coremio/modules/Servers/VirtFusion/
   ├── VirtFusion.php
   ├── api.php
   ├── config.php
   ├── lang/
   │   ├── ar.php
   │   ├── chinese.php
   │   ├── de.php
   │   ├── en.php
   │   ├── es.php
   │   ├── fr.php
   │   ├── pt-PT.php
   │   ├── pt.php
   │   └── zh-CN.php
   └── pages/
       ├── clientAreahome.php
       ├── create-form-elements.php
       └── order-detail.php
   ```

## Configuration

### 1. Create a VirtFusion API Token

In your VirtFusion admin panel, generate an API token with permissions covering:
- `connect`
- Packages & Hypervisor Groups (read)
- Media / OS Templates (read)
- Users (read, create, authentication tokens, reset password by external relation)
- Servers (read, create, build, delete, power, suspend/unsuspend, VNC, network/traffic)

### 2. Add the Server in WiseCP

1. Navigate to **Services → Hosting Management → Server Settings → Add New Server**.
2. Select **VirtFusion** as the Server Automation Type.
3. Fill in:
   - **Hostname:** Your VirtFusion panel hostname (e.g. `panel.example.com`)
   - **Port:** `443` (or your custom HTTPS port)
   - **SSL:** Enabled (recommended)
   - **Access Hash:** Your VirtFusion Bearer API token
4. Click **Test Connection** to verify connectivity (`GET /api/v1/connect`).

### 3. Configure Products

1. Edit or create a VPS product in WiseCP and open the **Core** tab.
2. Select your VirtFusion server.
3. Choose the **Package**, **Location (Hypervisor Group)** (or `-1` for VirtFusion auto-selection), **Default Operating System**, **IPv4 Quantity**, and **Enable IPv6**.

## Security Notes

- **Never commit** your VirtFusion API token or server hostnames to version control; all credentials are stored exclusively inside WiseCP's Server Settings.
- TLS verification is enabled by default (`$verify_ssl = true` in `VirtFusionAPI`). Always use a valid TLS certificate on your VirtFusion control panel.
- See [SECURITY.md](SECURITY.md) for reporting security vulnerabilities.

## Contributing

Contributions, bug reports, and pull requests are welcome! Please make sure any new PHP code passes `php -l` cleanly and preserves HTML/JS escaping and log redaction.

## License

This project is licensed under the **MIT License** — see the [LICENSE](LICENSE) file for details.
