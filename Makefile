all: homer.css homer.js

homer.css: src/homer.css
	postcss src/homer.css --use autoprefixer > homer.css

homer.js: src/homer.ts.php
	cd src && php -f homer.ts.php > ../homer.ts
	tsc --strict homer.ts --outFile homer.js

clean:
	rm -rf homer.css homer.js homer.ts
