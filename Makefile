homer:
	cd src && php -f homer.js.php > ../homer.js
	#postcss src/opcode-table.css --use autoprefixer > opcode-table.css

clean:
	rm -rf homer.js
