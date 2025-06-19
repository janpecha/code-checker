<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Tasks;

	use JP\CodeChecker\File;
	use Latte;
	use Nette;
	use Nette\Utils\Strings;


	class Tasks
	{
		private function __construct()
		{
		}


		public static function controlCharactersChecker(File $file): void
		{
			if ($m = Strings::match($file->contents, '#[\x00-\x08\x0B\x0C\x0E-\x1F]#', PREG_OFFSET_CAPTURE)) {
				$file->reportError('Contains control characters', self::offsetToLine($file->contents, $m[0][1]));
			}
		}


		public static function bomFixer(File $file): void
		{
			if (substr($file->contents, 0, 3) === "\xEF\xBB\xBF") {
				$file->reportFix('contains BOM', 1);
				$file->contents = substr($file->contents, 3);
			}
		}


		public static function utf8Checker(File $file): void
		{
			if (!Strings::checkEncoding($file->contents)) {
				preg_match('/^(?:[\xF0-\xF7][\x80-\xBF][\x80-\xBF][\x80-\xBF]|[\xE0-\xEF][\x80-\xBF][\x80-\xBF]|[\xC0-\xDF][\x80-\xBF]|[\x00-\x7f])*+/', $file->contents, $m);
				$file->reportError('Is not valid UTF-8 file', self::offsetToLine($file->contents, strlen($m[0]) + 1));
			}
		}


		public static function invalidPhpDocChecker(File $file): void
		{
			foreach (@token_get_all($file->contents) as $token) { // @ can trigger error
				if ($token[0] === T_COMMENT && Strings::match($token[1], '#/\*(?!\*).*(?<!\w)@[a-z]#isA')) {
					$file->reportWarning('Missing /** in phpDoc comment', $token[2]);

				} elseif ($token[0] === T_COMMENT && Strings::match($token[1], '#/\*\*(?!\s).*(?<!\w)@[a-z]#isA')) {
					$file->reportWarning('Missing space after /** in phpDoc comment', $token[2]);
				}
			}
		}


		public static function shortArraySyntaxFixer(File $file): void
		{
			$out = '';
			$brackets = [];
			try {
				$tokens = @token_get_all($file->contents, TOKEN_PARSE); // @ can trigger error
			} catch (\ParseError $e) {
				return;
			}

			for ($i = 0; $i < count($tokens); $i++) {
				$token = $tokens[$i];
				if ($token === '(') {
					$brackets[] = false;

				} elseif ($token === ')') {
					$token = array_pop($brackets) ? ']' : ')';

				} elseif (is_array($token) && $token[0] === T_ARRAY) {
					$a = $i + 1;
					if (isset($tokens[$a]) && $tokens[$a][0] === T_WHITESPACE) {
						$a++;
					}
					if (isset($tokens[$a]) && $tokens[$a] === '(') {
						$file->reportFix('uses old array() syntax', $token[2]);
						$i = $a;
						$brackets[] = true;
						$token = '[';
					}
				}
				$out .= is_array($token) ? $token[1] : $token;
			}
			$file->contents = $out;
		}


		public static function strictTypesDeclarationChecker(File $file): void
		{
			$declarations = '';
			$tokens = @token_get_all($file->contents); // @ can trigger error
			for ($i = 0; $i < count($tokens); $i++) {
				if ($tokens[$i][0] === T_DECLARE) {
					while (isset($tokens[++$i]) && $tokens[$i] !== ';') {
						$declarations .= is_array($tokens[$i])
							? $tokens[$i][1]
							: $tokens[$i];
					}
				} elseif (!in_array($tokens[$i][0], [T_OPEN_TAG, T_WHITESPACE, T_COMMENT, T_DOC_COMMENT], true)) {
					break;
				}
			}
			if (!preg_match('#\bstrict_types\s*=\s*1\b#', $declarations)) {
				$file->reportError('Missing declare(strict_types=1)');
			}
		}


		public static function invalidDoubleQuotedStringChecker(File $file): void
		{
			$prev = null;
			foreach (@token_get_all($file->contents) as $token) { // @ can trigger error
				if (($token[0] === T_ENCAPSED_AND_WHITESPACE && ($prev[0] !== T_START_HEREDOC || !strpos($prev[1], "'")))
					|| ($token[0] === T_CONSTANT_ENCAPSED_STRING && $token[1][0] === '"')
				) {
					$m = Strings::match($token[1], '#^([^\\\\]|\\\\[\\\\nrtvefxu0-7\W])*+#'); // more strict: '#^([^\\\\]|\\\\[\\\\nrtvefu$"x0-7])*+#'
					if ($token[1] !== $m[0]) {
						$file->reportWarning('Invalid escape sequence ' . substr($token[1], strlen($m[0]), 2) . ' in double quoted string', $token[2]);
					}
				}
				$prev = $token;
			}
		}


		public static function docSyntaxtHinter(File $file): void
		{
			$prev = null;
			foreach (@token_get_all($file->contents) as $token) { // @ can trigger error
				if (($token[0] === T_ENCAPSED_AND_WHITESPACE && $prev[0] !== T_START_HEREDOC
						|| $token[0] === T_CONSTANT_ENCAPSED_STRING)
					&& strpos($token[1], "\n") !== false
					&& (strpos($token[1], "\\'") !== false || strpos($token[1], '\\"') !== false)
				) {
					$file->reportWarning('Tip: use NOWDOC or HEREDOC', $token[2]);
				}
				$prev = $token;
			}
		}


		public static function newlineNormalizer(File $file): void
		{
			$new = str_replace("\n", PHP_EOL, str_replace(["\r\n", "\r"], "\n", $file->contents));
			if ($new !== $file->contents) {
				$file->reportFix('contains non-system line-endings', self::offsetToLine($file->contents, strlen(Strings::findPrefix([$file->contents, $new]))));
				$file->contents = $new;
			}
		}


		public static function trailingPhpTagRemover(File $file): void
		{
			$tmp = rtrim($file->contents);
			if (substr($tmp, -2) === '?>') {
				$file->reportFix('contains closing PHP tag ?>', self::offsetToLine($file->contents, strlen($tmp) - 1));
				$file->contents = substr($tmp, 0, -2);
			}
		}


		public static function phpSyntaxChecker(File $file): void
		{
			if (
				preg_match('#@phpVersion\s+([0-9.]+)#i', $file->contents, $m)
				&& version_compare(PHP_VERSION, $m[1], '<')
			) {
				return;
			}
			$php = defined('PHP_BINARY') ? PHP_BINARY : 'php';
			$stdin = tmpfile();
			fwrite($stdin, $file->contents);
			fseek($stdin, 0);
			$process = proc_open(
				$php . ' -l -d display_errors=1',
				[$stdin, ['pipe', 'w'], ['pipe', 'w']],
				$pipes,
				null,
				null,
				['bypass_shell' => true]
			);
			if (!is_resource($process)) {
				$file->reportWarning('Unable to lint PHP code');
				return;
			}
			$error = stream_get_contents($pipes[1]);
			if (proc_close($process)) {
				$error = strip_tags(explode("\n", $error)[1]);
				$line = preg_match('# on line (\d+)$#', $error, $m) ? (int) $m[1] : null;
				$file->reportError('Invalid PHP code: ' . $error, $line);
			}
		}


		public static function latteSyntaxChecker(File $file): void
		{
			$latte = new Latte\Engine;
			$latte->setLoader(new Latte\Loaders\StringLoader);

			try {
				$code = $latte->compile($file->contents);
				$fileToCheck = new File($file->getPath(), $code);
				static::phpSyntaxChecker($fileToCheck);

				foreach ($fileToCheck->getResult() as $message) {
					$file->report($message);
				}

			} catch (Latte\CompileException $e) {
				if (!preg_match('#Unknown (tag|macro|attribute)#A', $e->getMessage())) {
					$file->reportError($e->getMessage(), $e->sourceLine);
				} else {
					$file->reportWarning($e->getMessage(), $e->sourceLine);
				}
			}
		}


		public static function neonSyntaxChecker(File $file): void
		{
			try {
				Nette\Neon\Neon::decode($file->contents);
			} catch (Nette\Neon\Exception $e) {
				$line = preg_match('# on line (\d+)#', $e->getMessage(), $m) ? (int) $m[1] : null;
				$file->reportError($e->getMessage(), $line);
			}
		}


		public static function jsonSyntaxChecker(File $file): void
		{
			try {
				Nette\Utils\Json::decode($file->contents);
				if (trim($file->contents) === '') {
					$file->reportError('Syntax error');
				}
			} catch (Nette\Utils\JsonException $e) {
				$file->reportError($e->getMessage());
			}
		}


		public static function yamlIndentationChecker(File $file): void
		{
			if (preg_match('#^\t#m', $file->contents, $m, PREG_OFFSET_CAPTURE)) {
				$file->reportError('Used tabs to indent instead of spaces', self::offsetToLine($file->contents, $m[0][1]));
			}
		}


		public static function trailingWhiteSpaceFixer(File $file): void
		{
			$new = Strings::replace($file->contents, '#[\t ]+(\r?\n)#', '$1'); // right trim
			$eol = preg_match('#\r?\n#', $new, $m) ? $m[0] : PHP_EOL;
			$new = rtrim($new); // trailing trim
			if ($new !== '') {
				$new .= $eol;
			}
			if ($new !== $file->contents) {
				$bytes = strlen($file->contents) - strlen($new);
				$len = min(strlen($file->contents), strlen(Strings::findPrefix([$file->contents, $new])) + 1);
				$file->reportFix("$bytes bytes of whitespaces", self::offsetToLine($file->contents, $len));
				$file->contents = $new;
			}
		}


		public static function tabIndentationChecker(File $file, ?string $origContents = null): void
		{
			$origContents = $origContents ?: $file->contents;
			$offset = 0;
			if (preg_match('#^(\t*+)\ (?!\*)\s*#m', $file->contents, $m, PREG_OFFSET_CAPTURE)) {
				$file->reportError(
					$m[1][0] ? 'Mixed tabs and spaces to indent' : 'Used space to indent instead of tab',
					self::offsetToLine($origContents, $m[0][1])
				);
				$offset = $m[0][1] + strlen($m[0][0]) + 1;
			}
			if (preg_match('#(?<=[\S ])(?<!^//)\t#m', $file->contents, $m, PREG_OFFSET_CAPTURE, $offset)) {
				$file->reportError('Found unexpected tabulator', self::offsetToLine($origContents, $m[0][1]));
			}
		}


		public static function tabIndentationPhpChecker(File $file): void
		{
			$s = '';  // remove strings from code
			foreach (@token_get_all($file->contents) as $token) { // @ can trigger error
				if (
					is_array($token)
					&& in_array($token[0], [T_ENCAPSED_AND_WHITESPACE, T_CONSTANT_ENCAPSED_STRING], true)
				) {
					$token[1] = preg_replace('#[\t ]#', '.', $token[1]);
				}
				$s .= is_array($token) ? $token[1] : $token;
			}
			$fileToCheck = new File($file->getPath(), $s);
			self::tabIndentationChecker($fileToCheck, $file->contents);

			foreach ($fileToCheck->getResult() as $message) {
				$file->report($message);
			}
		}


		public static function unexpectedTabsChecker(File $file): void
		{
			if (($pos = strpos($file->contents, "\t")) !== false) {
				$file->reportError('Found unexpected tabulator', self::offsetToLine($file->contents, $pos));
			}
		}


		private static function offsetToLine(string $s, int $offset): int
		{
			return $offset ? substr_count($s, "\n", 0, $offset) + 1 : 1;
		}
	}
