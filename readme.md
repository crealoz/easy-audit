
# EasyAudit Module

This module is designed to provide auditing capabilities for Magento applications.

## Installation

1. Clone the repository:
```bash
git clone git@github.com:crealoz/easy-audit.git
```
2. Navigate to the project directory:
```bash
cd easy-audit
```
3. Install the module via Composer:
```bash
composer install
```

## Usage

Audit can be run using magento CLI command:

```bash
php bin/magento crealoz:run:audit
```

## How to add a new audit subject?

### General considerations

There is a single entry point for the audit process, which is the `\Crealoz\EasyAudit\Service\Audit` class. This class is
responsible for running the audit process and handling the audit results. The audit process will loop through the list of
types of audit subjects and run the audit processes for each of them.

### Create a new type of audit subject

For the moment, the audit process is divided into two types: `xml` and `php`. If you want to create a new type, you need
to create a new class that implements the `TypeInterface` interface. The class should be located in the `Service\Type`
directory. The new class can extend the `AbstractType` class, which provides a default implementation for the `TypeInterface`.

The new class should be registered in the `di.xml` file, in the `typeMapping` arguments of the class `Crealoz\EasyAudit\Service\Type\TypeFactory`.
Please note that the entry in the `typeMapping` arguments should be in the format `type => class` and type will be used
to identify the type of the audit subject for the `processors` of `\Crealoz\EasyAudit\Service\Audit`.

### Create a new audit subject

Create a new class that implements `ProcessorInterface`. The class should be located in the `Service\Processor` directory.
It can extend the `AbstractProcessor` class, which provides a default implementation for the `ProcessorInterface` methods.

### Register the new audit subject

In `di.xml` file, add a new `item` node to the `processor` arguments of the class `Crealoz\EasyAudit\Service\Audit`.
Please note that the processors are divided by _types_ (e.g. : di, view...) and if you want to create a new type. The 
logic have to be implemented and the new type have to implement the `Crealoz\EasyAudit\Service\Processor\ProcessorInterface`
interface.


## Contributing

Contributions are welcome. Please make sure to update tests as appropriate.

## License

[MIT](https://choosealicense.com/licenses/mit/)

Please note that you should replace the placeholders with the actual information about your module.