Changelog

23/11/2026 - v1.0.1

Minor versioning - the module has the following changes:

» Reverified with Gemini 4 Neon for code quality and security; 4 security bugs and 5 quality-of-life bugs were corrected

» **From this date onwards, this module has been open-sourced and it's available for everyone. This is the first open-source module I have published in GitHub.** 

» First release with published files and direct .tar.gz packaging.

01/06/2026 - v1.0.0.2

Minor versioning to attest new certification following new software launches:

» Certified as working and supporting: VirtFusion 7.0+ (launched May 21st, 2026)

» Certified as working and supporting: VirtFusion v3.1.9.8+ (launched April / May 2026)

» Certified as working and supporting: PHP 8.2, 8.3, 8.4 and 8.5, without any errors (confirmed May 30/31st, 2026)

01/03/2026 - v1.0.0.1

This module has the following changes:

» Removes the second placeholder badge that existed in the Client Area, which incorrectly marked VPS services as Offline even when they were provisioned and working;

» Fixes the formatting and display issue that was occurring with custom templates outside of Modern (WiseCP's default), and now properly expands and shrinks on all screens;

» When a VPS is pending provisioning, it now says ‘Pending User Setup’ with a green background (the same message is displayed in all languages). Once provisioned, it no longer shows a badge;

» Improved hostname fetch, which now fetches an FQDN address by default and, if this does not exist (since the field is optional), falls back and fetches the VPS name, as a literal interpretation of ‘host name’. 

» Improved interpretation for the end user: when installation is still pending, it now displays ‘N/A’ to the left of ‘Pending User Setup’, which allows for the appropriate interpretation needed to perform the initial configuration.

22/12/2025 - v1.0.0 - Developed by Tiago Pereira

Launch of the integration module for WiseCP. For the full feature list, check the readme or readme-pt documents.
