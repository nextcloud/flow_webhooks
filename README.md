# Flow Webhooks

## Endpoints

Every user and the instance have each one unique endpoint where webhooks can be directed to. The identifying piece it ten characters long and created once when opening the Webhook settings for the first time.

The URL follows this pattern: `https://my.nxtcld.srv/ocs/v2.php/apps/flow_webhooks/api/v1/hook/{ENDPOINT_ID}`

## Profiles

A profile matches to an incoming request and defines the presentation of it within the possible Nextcloud operations.

There are three ways to introduce a profile to your instance:
* by app listening to the `OCA\FlowWebhooks\Events\RegisterProfile` event and calling it's `addProfile` method with an instance of `OCA\FlowWebhooks\Model\Profile`. This is globally available.
* 🏗️ by an Administrator through instance's Webhooks settings
* 🏗️ by an user through personal Webhooks settings

Profiles added by app or admin settings are valid system wide and are priorized over personally added profiles. A profile added in the personal settings is only valid for the user who created it.

When a request comes in, the webhook service matches available profile against the request. The first profile that matches is the valid one, others are discarded. The tests happen in the order of: app profiles, admin profiles, user profiles.

Criteria for matches are request headers and parameters. One criterion consists of the name of the header or parameter, and a regular expression pattern. Several requirements are de facto and-connected, i.e. everything has to match.

Further a profile consists of templates for:

* display text in verbosity levels of 0-3
* an URL
* an Icon URL

Within a template identifiers within double curly brackets are replaced with data from request parameters. Example: in the text template `'{{comment.user.login}} says {{ comment.body }}'` there are two identifiers: `comment.user.login` and `comment.body`. If the request payload has those values set, they will be replaces accordingly, and otherwise with by `(?)`. When extracting identifier, the app ignores single space characters between the doubled curly brackets. 

Furthermore, profiles have a name property intended to be easily identifiable for users.
