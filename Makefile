homer:
	cd src && php -f homer.ts.php > ../homer.ts
	tsc --strict homer.ts --outFile homer.js
	postcss src/homer.css --use autoprefixer > homer.css

clean:
	rm -rf homer.css homer.js homer.ts
