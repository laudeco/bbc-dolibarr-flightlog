[![Scrutinizer Code Quality](https://scrutinizer-ci.com/g/laudeco/bbc-dolibarr-flightlog/badges/quality-score.png?b=master)](https://scrutinizer-ci.com/g/laudeco/bbc-dolibarr-flightlog/?branch=master)

[![wercker status](https://app.wercker.com/status/06514c2e434e17789f8ac5840bef3d5a/s/master "wercker status")](https://app.wercker.com/project/byKey/06514c2e434e17789f8ac5840bef3d5a)

This is a module for [Dolibarr](http://dolibarr.org), it cannot leave outside of Dolibarr.

# What Dolibarr is ?
Dolibarr ERP & CRM is a modern and easy to use software package to manage your business.

# Flight log - The module
In the world of the air navigation, each pilots need to keep a log of each flights they do. 
This modules manages the log of each pilot in the association. 
Since we do not pilot a plane but a hot air balloon, some flight information may not be the same (eg. the take off location and the landing one are not necessarily an airport).

but every information required are present in the current module. We have :
* The flight type
* Take off time & location
* Landing time & location
* Date
* Hot air balloon
* The pilot information
* Passengers information
* Some billing information


## Highly coupled
This module is highly coupled to our company. Sorry about that, if someone else want to use it, I can work on the fact to extract the 2 modules. That being said, it doesn't mean you cannot use this for your own business. Everything is configurable through the administration of Dolibarr. 

## Why didn't you develop this as a side project?
Since we are using Dolibarr for our accountacy and our management, we didn't want to develop a side project to manage our flights. By adding the flight log as a module we can ... 
* Create bills
* Use the power of Dolibarr about the user management..
* Extract data with the already present tools
* Using the e-mail structure
* ...

## What's next ?
* Adding an API to be able to develop a mobile application.
* Adding some statistics over the years.
* Improving the e-mail automatically sent on incident, ...
* Fixing here and there some small issues

 
# Flight types
Every flight type is fully managed from the administration page of the module (_Configuration > Modules > Carnet de vol_).
A type carries its number (T1, T2, ...), its name, the service used to invoice it and the following rules :

| Field | Meaning |
| --- | --- |
| Points / montant | Points won (mission) or amount charged to the pilot per flight. Left empty, the price of the linked service is used. |
| Mission | The flight is a mission for the club : it gives points to the pilot, the organisator wins points too and the flight is reimbursed through the expense notes (kilometers + lump sum). |
| Pax | The number of passengers is required. |
| A facturer | The flight has to be invoiced to a customer. |
| Instruction | Instruction flight : the organisator is the instructor and wins the instructor points. |
| Facturé au pilote | The flight is charged to the pilot on his yearly bill. |
| €/km | Kilometer allowance reimbursed to the pilot on his expense note. Left empty, the value of the module (`BBC_FLIGHT_LOG_TAUX_REMB_KM`) is used. |
| Forfait € | Lump sum reimbursed to the pilot for one flight of this type. Left empty, the value of the module (`BBC_FLIGHT_LOG_UNIT_PRICE_MISSION`) is used. |
| Actif | The type can be selected on a new flight. |

The recap table of the module, the pilot bills and the expense notes are built from those rules: the missions of the club
are grouped together with their own sub total of points, all the other types are grouped with their own sub total in euro.
The reimbursement of a pilot is computed type by type, with the allowances of each type.
A type may be added, renamed, renumbered, disabled and - as long as no flight uses it - deleted.

# Installation

At the moment the tested way is :
1. Download the module
2. Extract or move it to _htdocs/flightlog_
3. Go in your configuration and enable the module.