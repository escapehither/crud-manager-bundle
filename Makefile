## start make file

DOCKER-COMPOSE= docker-compose
SYMFONY_DIR=.
SYMFONY_VENDOR_BIN_DIR = vendor/bin
PHPUNIT=$(SYMFONY_VENDOR_BIN_DIR)/simple-phpunit
BEHAT=$(SYMFONY_VENDOR_BIN_DIR)/behat
PHPCS_PROJECT=$(SYMFONY_VENDOR_BIN_DIR)/phpcs
PHPCBF_PROJECT=$(SYMFONY_VENDOR_BIN_DIR)/phpcbf

cs-vendor-lunch: #install symfony coding standard and lunch
cs-vendor-lunch: cs-vendor-install-standard cs-vendor


#Coding standard
cs-locale: 
	phpcs
cs-vendor: 
	$(PHPCS_PROJECT) 
cs-vendor-summary: 
	$(PHPCS_PROJECT) -n --report=summary
cs-vendor-install-standard: 
	$(PHPCS_PROJECT) --config-set installed_paths vendor/escapestudios/symfony2-coding-standard
cbf-vendor: 
	$(PHPCBF_PROJECT)
#Base
console:	
	$(CONSOLE)

#test
run-test:
	php -d memory_limit=-1 $(PHPUNIT) --configuration phpunit.xml.dist --coverage-text --colors=never
# add test on a specific method "phpunit --filter methodName ClassName path/to/file.php"

 
