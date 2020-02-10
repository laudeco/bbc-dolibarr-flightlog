<?php
dol_include_once('/includes/autoload.php');

//traits
dol_include_once("/flightlog/class/Common/ValueObject/Id.php");

dol_include_once("/flightlog/lib/flightLog.lib.php");

dol_include_once('/flightlog/class/card/Tab.php');
dol_include_once('/flightlog/class/card/TabCollection.php');

dol_include_once('/flightlog/class/bbcvols.class.php');
dol_include_once('/flightlog/class/bbctypes.class.php');

dol_include_once('/flightlog/class/GraphicalData.php');
dol_include_once('/flightlog/class/GraphicalType.php');
dol_include_once('/flightlog/class/GraphicalValue.php');
dol_include_once('/flightlog/class/GraphicalValueType.php');
dol_include_once('/flightlog/class/YearGraphicalData.php');

dol_include_once('/flightlog/class/flight/FlightBonus.php');
dol_include_once('/flightlog/class/flight/FlightPoints.php');
dol_include_once('/flightlog/class/flight/FlightTypeCount.php');
dol_include_once('/flightlog/class/flight/Pilot.php');

dol_include_once('/flightlog/class/missions/PilotMissions.php');
dol_include_once('/flightlog/class/missions/FlightMission.php');
dol_include_once('/flightlog/class/missions/QuarterMission.php');
dol_include_once('/flightlog/class/missions/QuarterPilotMissionCollection.php');

dol_include_once('/flightlog/class/Damage/DamageAmount.php');
dol_include_once('/flightlog/class/Damage/FlightDamage.php');
dol_include_once('/flightlog/class/Damage/FlightId.php');
dol_include_once('/flightlog/class/Damage/DamageId.php');
dol_include_once('/flightlog/class/Damage/AuthorId.php');

dol_include_once('/flightlog/exceptions/NoMissionException.php');

dol_include_once('/flightlog/query/BillableFlightQuery.php');
dol_include_once('/flightlog/query/BillableFlightQueryHandler.php');
dol_include_once('/flightlog/query/GetPilotsWithMissionsQueryHandler.php');
dol_include_once('/flightlog/query/GetPilotsWithMissionsQuery.php');
dol_include_once('/flightlog/query/FlightForQuarterAndPilotQuery.php');
dol_include_once('/flightlog/query/FlightForQuarterAndPilotQueryHandler.php');
dol_include_once('/flightlog/Application/Damage/Query/GetDamagesForFlightQueryRepositoryInterface.php');
dol_include_once('/flightlog/Application/Damage/Query/GetPilotDamagesQueryRepositoryInterface.php');

dol_include_once('/flightlog/command/CommandHandlerInterface.php');
dol_include_once('/flightlog/command/CommandInterface.php');
dol_include_once('/flightlog/command/CreateExpenseNoteCommandHandler.php');
dol_include_once('/flightlog/command/CreateExpenseNoteCommand.php');
dol_include_once('/flightlog/command/ClassifyFlightHandler.php');
dol_include_once('/flightlog/command/ClassifyFlight.php');
dol_include_once('/flightlog/command/CreateFlightBillCommand.php');
dol_include_once('/flightlog/command/CreateFlightBillCommandHandlerFactory.php');
dol_include_once('/flightlog/Application/Damage/Command/CreateDamageCommand.php');
dol_include_once('/flightlog/Application/Damage/Command/CreateDamageCommandHandler.php');
dol_include_once('/flightlog/Application/Damage/Command/InvoiceDamageCommand.php');
dol_include_once('/flightlog/Application/Damage/Command/InvoiceDamageCommandHandler.php');
dol_include_once('/flightlog/Application/Common/ViewModel/ViewModel.php');
dol_include_once('/flightlog/Application/Damage/ViewModel/Damage.php');
dol_include_once('/flightlog/Application/Damage/ViewModel/TotalDamage.php');

dol_include_once('/flightlog/validators/ValidatorInterface.php');
dol_include_once('/flightlog/validators/AbstractValidator.php');
dol_include_once('/flightlog/validators/FlightValidator.php');
dol_include_once('/flightlog/validators/SimpleOrderValidator.php');

dol_include_once('/flightlog/Form/FormElementInterface.php');
dol_include_once('/flightlog/Form/FormInterface.php');
dol_include_once('/flightlog/Form/BaseInput.php');
dol_include_once('/flightlog/Form/Form.php');
dol_include_once('/flightlog/Form/Hidden.php');
dol_include_once('/flightlog/Form/Input.php');
dol_include_once('/flightlog/Form/InputTime.php');
dol_include_once('/flightlog/Form/InputDate.php');
dol_include_once('/flightlog/Form/InputTextarea.php');
dol_include_once('/flightlog/Form/Number.php');
dol_include_once('/flightlog/Form/Select.php');
dol_include_once('/flightlog/Form/FlightTypeSelect.php');
dol_include_once('/flightlog/Form/UserSelect.php');
dol_include_once('/flightlog/Form/BalloonSelect.php');
dol_include_once('/flightlog/Form/SimpleFormRenderer.php');
dol_include_once('/flightlog/Form/FlightForm.php');

dol_include_once('/flightlog/Http/Web/Controller/WebController.php');
dol_include_once('/flightlog/Http/Web/Controller/AddFlightDamageController.php');
dol_include_once('/flightlog/Http/Web/Controller/FlightDamageController.php');
dol_include_once('/flightlog/Http/Web/Controller/DamageController.php');
dol_include_once('/flightlog/Http/Web/Controller/FlightController.php');

dol_include_once('/flightlog/Http/Web/Requests/Request.php');
dol_include_once('/flightlog/Http/Web/Response/Response.php');
dol_include_once('/flightlog/Http/Web/Response/Redirect.php');

dol_include_once('/flightlog/Http/Web/Form/DamageCreationForm.php');
dol_include_once('/flightlog/Http/Web/Form/SupplierBillSelect.php');

dol_include_once('/flightlog/Infrastructure/Common/Repository/AbstractDomainRepository.php');
dol_include_once('/flightlog/Infrastructure/Damage/Repository/FlightDamageRepository.php');
dol_include_once('/flightlog/Infrastructure/Damage/Query/Repository/GetDamageQueryRepository.php');
dol_include_once('/flightlog/Infrastructure/Damage/Query/Repository/GetDamagesForFlightQueryRepository.php');
dol_include_once('/flightlog/Infrastructure/Damage/Query/Repository/GetPilotDamagesQueryRepository.php');
dol_include_once('/flightlog/Infrastructure/Common/Routes/Route.php');
dol_include_once('/flightlog/Infrastructure/Common/Routes/RouteManager.php');
dol_include_once('/flightlog/Infrastructure/Common/Routes/Guard.php');

dol_include_once('/core/lib/ajax.lib.php');
dol_include_once('/core/lib/price.lib.php');


dol_include_once('/fourn/class/fournisseur.facture.class.php');
