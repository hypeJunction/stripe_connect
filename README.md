Stripe Connect
==============

Stripe Connect API Layer for Elgg


## Webhooks

To ensure that Elgg receives the crucial updates, please set up your Stripe Connect
webhooks as follows:

**Testing**
https://YOUR-SITE/services/api/rest/json?method=stripe.connect.webhooks&environment=sandbox

**Live**
https://YOUR-SITE/services/api/rest/json?method=stripe.connect.webhooks&environment=production


## OAuth Endpoints

OAuth Redirect URI should include the following endpoint:

**Testing**
https://YOUR-SITE/stripe_connect/connected/sandbox

**Live**
https://YOUR-SITE/stripe_connect/connected/production
