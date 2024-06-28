<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Rules\Php;

	use JP\CodeChecker\CommitMessage;
	use JP\CodeChecker\FileContent;
	use JP\CodeChecker\PhpRule;
	use JP\CodeChecker\Reporter;
	use JP\CodeChecker\Utils;


	class DeclareStrictTypesRule extends PhpRule
	{
		public function getCommitMessage(): ?CommitMessage
		{
			return new CommitMessage(
				subject: 'Added declare(strict_types=1)'
			);
		}


		public function processContent(
			FileContent $fileContent,
			Reporter $reporter
		): void
		{
			$contents = $fileContent->contents;
			$declarations = Utils\PhpCode::getDeclarations($contents);

			if (!preg_match('#\bstrict_types\s*=\s*1\b#', implode("\n", $declarations))) {
				if (str_starts_with($contents, '<?php')) {
					$reporter->reportFixInFile('Missing declare(strict_types=1)', $fileContent->getFile());
					$indent = Utils\FileContent::detectIndentation($contents);
					$fileContent->contents = "<?php\n\n" . $indent . "declare(strict_types=1);" . substr($contents, 5);

				} else {
					$reporter->reportErrorInFile('Missing declare(strict_types=1)', $fileContent->getFile());
				}
			}
		}
	}
