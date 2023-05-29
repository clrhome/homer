homer:
	postcss src/homer.css --use autoprefixer > homer.css
	cd src && php -f homer.ts.php > ../homer.ts
	tsc --strict homer.ts --outFile homer.js

clean:
	rm -rf homer.css homer.js homer.ts
