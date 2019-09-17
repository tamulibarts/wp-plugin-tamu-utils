# wp-plugin-tamu-utilities
Collection of TAMU-specific utilities for WordPress

### Add User page
Changes the `Add User` page for child sites (not the network admin `Add User` page) to accept a list of TAMU NetIDs and a role level.
The plugin validates the NetIDs for validity, fetches basic directory information (names and email address), creates the wp user records as needed, adds the users to the site and reports failures and successes to the administrator.
Requires a few constants be defined to interact with NetID validation service:
- `MQS_HOST`: hostname of the MQS identity service
- `MQS_IDENTIFIER`: client identifier of this integration from MQS
- `MQS_SHARED_SECRET`: shared secret for the above client identifier

The plugin will also send a welcome email to added user(s) with a link to the admin dashboard URL.

### Department Admin Role
Special role adding customizer access on top of everything Editor can do

### CAS Login
Changes the login mechanism to use CAS rather than wordpress passwords. Supports logout.
Requires a constant be defined called `CAS_SERVER`, which is the hostname of your CAS server.


### SMTP Server Integration
Enables and configures PHPMailer to use a campus SMTP service. Requires a few constants be defined:
- `SMTP_HOST`: hostname of SMTP server
- `SMTP_PORT`: port to connect to SMTP server on
- `SMTP_FROM`: from address used on sent emails
- `SMTP_NAME`: from name user on sent emails