# virtfusion-for-wisecp
An integration module for WiseCP that connects VirtFusion's API to WiseCP and allows for immediate deployment.

****************************************************
WiseCP Integration Module for the VirtFusion API
****************************************************
![VirtFusion for WiseCP](https://github.com/alfatarsos/virtfusion-for-wisecp/blob/main/Virt-Fusion-on-Wise-CP-Client-Area.png)

# INTRODUCTION

The WiseCP billing system has several integrations available, including, among others, SolusVM 2, Virtualizor, and AutoVM. However, until now, it lacked an integration that allowed VPS provisioning in VirtFusion, either through a module provided by WiseCP itself – as often happens – or through a module provided by VirtFusion, as a natural stakeholder in this market.

The now-ready and available integration addresses this situation, allowing the WiseCP billing system to now enable automatic provisioning and management of VPS provisioned from VirtFusion management nodes, substantially simplifying the lives of clients, providers, and support.

# WHY DESIGN AN INTEGRATION FOR VIRTFUSION

The VirtFusion system is one of the most widely used provisioning and management systems in this sector, and it is also the system that best suits the preferences of consumers who purchase this type of service (VPS/VDS), as well as many service providers. Among the three main options available – SolusVM, Virtualizor, and VirtFusion – it offers several advantages, namely a mature approach to a set of problems common to providers and clients; tested solutions that are relatively easy to understand and implement for those with a minimum of knowledge in Linux system administration; an interface that is simple, easy to use, and straightforward on the client side; and a particularly well-designed interface for the provider, with a set of system-exclusive functionalities that improve management and the final product for the client.

In turn, the WiseCP system, among the billing systems currently available on the market, is one of the most powerful and resilient, competing in several aspects with the current industry leader and possessing an extremely appealing and highly functional interface.

Designing an integration between both systems is, therefore, perfectly natural and reasonable, given that both converge on a consistent and improved user experience for the Client, with high quality of service, high availability, and easy management of the various elements necessary for both parties.

# COMPATIBILITY AND FUNCTIONALITIES OF THIS MODULE

The module designed here primarily integrates functions from the VirtFusion API v1.0, which is in use and continuously expanding its functionalities (as of 09-12-2025), and is only tested to work, at least, with the latest version of WiseCP (v3.1.9.7, released in November 2025).

Although no problems are expected with older versions of WiseCP (3.1.9.x), nor with version 5.0 under development – ​​provided that WiseCP does not break the logic of existing ecosystem integrations with other required coding – compatibility with any other version is not guaranteed, as it has only been tested on this version to date – test at your own risk.

If and when compatibility with future versions is confirmed, this file will be updated in due course.

Completed and now available features include:

- Automatic VPS provisioning in just 20 seconds;
- Automatic VPS suspension in up to 60 seconds, dependent on updating the PHP crontab in WiseCP;
- VPS cancellation, immediately inserted into the VirtFusion system, and executed after 5 minutes, as per API default;
- Integration of the latest VNC functionality in the User Area, created in November, in accordance with the new VirtFusion 6.1 version, through UUID extraction and automatic path formatting in the browser;
- Information in the Client Area on the status of your VPS: Online / Provisioning / Offline;
- Feedback in the Client Area on the execution of operations (Start / Restart / Shut Down / Force Stop);
- Direct connection to the VirtFusion Panel via external link, using 2 buttons (generally on the "Panel" button, for password reset on the respective button) - useful for topics such as NAT management or fine-tuning;
- Direct communication in the Client Area for password recovery to access the Panel without needing email, directly on the screen, for convenience;
- Summary information to the Client through dynamic parameters via API call, specifically:
    - Hostname associated in the VirtFusion system;
    - Main location (HypervisorGroup function in VirtFusion) and main IP (defaults IPv6 on any subnet; if none exists, assumes IPv4)
    - IPv4, IPv4 NAT and IPv6 available, in all formulations and ranges, up to IPv6 /128;
    - Dedicated server source IP, useful for NAT for the user;
    - Available Bandwidth, expressed in GB;
    - Number of vCPUs allocated in the System (supplemented by the information in the WiseCP Specifications)
    - GB of memory allocated in the System (supplemented by the information in the WiseCP Specifications)
    - Hard Disk allocated in the System (supplemented by the information in the WiseCP Specifications);
    - Operating System allocated in the System, dynamic parameter;
    - Name of the Dedicated Server to which the Client is allocated (reinforcement of information in the title bar);
    - Main location (reinforcement of information in the title bar).
- Certified and easy-to-perform operation on mobile devices, with large, multi-colored buttons that are easy to touch and easily represented, naturally adapting to the 1:1 format of these devices;
- Backoffice supports the following options in Product Settings » Core:
    - Define Service Plan to apply to the Client
    - Enable/Disable IPv6 (option provided by the VirtFusion API) - the number of IPs must be defined in the VirtFusion Back Office by the provider, applied to the Service Plan for which we will perform the deployment, in the respective Control Panel;
    - Number of IPv4s to Add (value between 0 and 4, allows IPv6-only, IPv4 NAT or Full IPv4 provisioning);
    - Manual Location ID (fallback system in case it is not possible to obtain HypervisorGroup);
    - Primary Location (HypervisorGroup function for deployment - with Auto option for the 1st location chosen by VirtFusion).
- Very appealing interface, gradually colored, easy for the Client to use and with more functions without leaving the Client Area compared to any of the currently existing VirtFusion integrations for WHMCS, Blesta, ClientExec, HostBill, Upmind and Paymenter - as is the normal and natural preference of the Client and as they would normally have in other systems (for example Virtualizor, SolusVM and Proxmox).

# TECHNICAL NOTES AND KNOWN BUGS

Naturally, integrating something like this into the WiseCP system has challenges and particularities, as happens with any integration. The existing ones for this module do not prevent its regular operation, but they should be mentioned.

- SSO only happens if the client is already logged in, for security reasons and different approaches to SSO by VirtFusion and WiseCP. If not, prior login is requested, and subsequent logins will remain active until the installed cookie expires or the client logs out of the VirtFusion area.
- VNC only works if the client has previously logged into VirtFusion. If not, a window will open asking them to do so.
- The "Set Panel Password" field, which does not require an email, forces the client to manually note the displayed key, as the field does not allow editing. This works this way by default but is also a security measure (prevents clipboard hijacking of sensitive data that could compromise the client's security).
- External connections to the Panel and "Password Reset" depend on the Hostname being redefined in the IP address in WiseCP. Otherwise, only the IP address will appear and the connection will not be established with SSL. This is why it is essential that the configuration is done correctly internally by the provider.
- Due to limitations arising from internal communication within the WiseCP system, the communication of Power functions (on/off/restart/force stop) and VPS status in the module is not directly synchronized with the VirtFusion system. Instead, it is handled semi-directly, using an "educated guess" system. This involves verifying the maximum reasonable time for each operation and providing the time allotted for completion on the respective VPS (25 seconds for Start/Restart, 40 seconds for Shutdown/Power Off). The client or Provider can always confirm the exact status of the VPS via VNC or by logging into the VirtFusion Panel to manage it – this will help with any technical errors.
- To ensure correct communication of Power functions and reliability in the final customer experience, the Provider is required to observe a minimum hard drive speed of 80 MB/s on 64k or higher, 40 MB/s on 4k, and avoid an excessively significant performance restriction on vCPUs, or iowait greater than 25%. Failure to observe any of these parameters by the Provider will substantially worsen the customer experience when interacting with the VPS in their Customer Area.
- The indication in the WiseCP system for the purchased VPS, above the information presented in this module, will always be "Online" (in green) and should be interpreted as the purchased package itself being active in the website's billing system, not representing any valid verification of the VPS status. Due to technical limitations, this status only appears in WiseCP as "Online", "Obtaining..." or empty (in cases of suspension).
- The Manual Location ID function (in Core) has not been previously tested.
- If you want to allocate more than one hard drive to a given Service Plan, it is not provisioned in this module, but rather in the Service Plan itself, along with the VirtFusion Management Node, in the appropriate area. Due to technical reasons, this option was not activated, despite being exposed in the API, as it was understood that configuration per plan is sufficient, and allocated disks are frequently fixed in their paths, even in High Availability situations.
- Assigning more than one IPv6 address during provisioning is not supported due to limitations of the VirtFusion API. If the Client wants more than one address or a higher subnet range, this must be managed in the Management Node itself.

# MODULE AVAILABILITY

This module will be available during the month of December, in a suitable location to be designated, for provisioning, once complete. For more information, a support request can be submitted on the c-servers.co.uk website.
