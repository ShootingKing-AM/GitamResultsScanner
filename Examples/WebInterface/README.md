# WebInterface

Another Implementation of ResultsScanner, This is a simple webinterface to get resultsDB. This is similar to StandAlonePHPScript, 
but will only execute when the user is still viewing the page. Once the page is closed the operations will immediately halt. 

In brief, This script uses Asynchronous-Javascript (AJAX) via JQuery to contact serverside script to get details about a(or a set of) RegID(s).
Since this is an AJAX request, multiple RegIDs can be queried at once which is faster compared to traditional queuing (done in StandAloneScript).

The required Set of RegIDs are to be Batched by the user and then the form is asynchronously submitted to server.
