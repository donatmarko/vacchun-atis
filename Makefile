run:
	php -S 127.0.0.1:8000

install:
	composer install --dev

clean:
	rm -r report
	rm -r vendor

