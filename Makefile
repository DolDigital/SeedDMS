VERSION=4.3.36
SRC=CHANGELOG inc conf utils index.php languages views op out doc styles LICENSE webdav install restapi pdfviewer
# webapp

NODISTFILES=utils/importmail.php utils/seedddms-importmail utils/remote-email-upload utils/remote-upload .svn .gitignore styles/blue styles/hc styles/clean views/blue views/hc views/clean

PHPDOC=~/Downloads/phpDocumentor-2.8.1/bin/phpdoc

dist:
	mkdir -p tmp/seeddms-$(VERSION)
	cp -a $(SRC) tmp/seeddms-$(VERSION)
	(cd tmp/seeddms-$(VERSION); rm -rf $(NODISTFILES))
	(cd tmp;  tar --exclude=.svn --exclude=.gitignore --exclude=views/blue --exclude=views/hc --exclude=views/clean --exclude=styles/blue --exclude=styles/hc --exclude=styles/clean -czvf ../seeddms-$(VERSION).tar.gz seeddms-$(VERSION))
	rm -rf tmp

pear:
	(cd SeedDMS_Core/; pear package)
	(cd SeedDMS_Lucene/; pear package)
	(cd SeedDMS_Preview/; pear package)
	(cd SeedDMS_SQLiteFTS/; pear package)

webdav:
	mkdir -p tmp/seeddms-webdav-$(VERSION)
	cp webdav/* tmp/seeddms-webdav-$(VERSION)
	(cd tmp; tar --exclude=.svn -czvf ../seeddms-webdav-$(VERSION).tar.gz seeddms-webdav-$(VERSION))
	rm -rf tmp

webapp:
	mkdir -p tmp/seeddms-webapp-$(VERSION)
	cp -a restapi webapp tmp/seeddms-webapp-$(VERSION)
	(cd tmp; tar --exclude=.svn -czvf ../seeddms-webapp-$(VERSION).tar.gz seeddms-webapp-$(VERSION))
	rm -rf tmp

doc:
	$(PHPDOC) -d SeedDMS_Core --ignore 'getusers.php,getfoldertree.php,config.php,reverselookup.php' --force -t html

apidoc:
	apigen  generate -s SeedDMS_Core --exclude tests -d html

.PHONY: webdav webapp
