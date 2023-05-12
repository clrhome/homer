homer:
	cd src && php -f homer.ts.php > ../homer.ts
	tsc --strict homer.ts --outFile homer.js
	#postcss src/opcode-table.css --use autoprefixer > opcode-table.css

clean:
	rm -rf homer.js homer.ts
