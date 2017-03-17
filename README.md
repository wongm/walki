# walki
How far is myki making you walk? This app will tell you!

Back in March 2014 Public Transport Victoria finally opened up the API which powers their mobile apps, so I decided to have a play around with it.

With the mobile landscape already littered with hundreds of different trip planning apps, I decided to build something slightly different, with this being the end result.

In the backend I'm using PHP to gather tram stop and myki retailer locations through calls to the PTV API, with the resulting data being mashed around in Javascript until they are drawn out on a pretty map.

The frontend code is all jQuery Mobile, with the maps being drawn using the Google Maps JavaScript API v3.

You can read the full backstory on my blog: https://wongm.com/2017/03/how-far-is-myki-making-you-walk/
