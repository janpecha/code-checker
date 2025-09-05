<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Rules\Php;

	use JP\CodeChecker\CommitMessage;
	use JP\CodeChecker\File;
	use JP\CodeChecker\Rules\FileRule;
	use JP\CodeChecker\Utils;


	class DeclareStrictTypesRule implements FileRule
	{
		public function getCommitMessage(): ?CommitMessage
		{
			return new CommitMessage(
				subject: 'Added declare(strict_types=1)'
			);
		}


		public function processFile(
			File $file
		): void
		{
			if (!$file->matchName(['*.php', '*.phpt'])) {
				return;
			}

			$contents = $file->contents;
			$declarations = Utils\PhpCode::getDeclarations($contents);

			if (!preg_match('#\bstrict_types\s*=\s*1\b#', implode("\n", $declarations))) {
				if (str_starts_with($contents, '<?php')) {
					$file->reportFix('Missing declare(strict_types=1)');
					$indent = Utils\FileContent::detectIndentation($contents);
					$file->contents = "<?php\n\n" . $indent . "declare(strict_types=1);" . substr($contents, 5);

				} else {
					$file->reportError('Missing declare(strict_types=1)');
				}
			}
		}
	}
