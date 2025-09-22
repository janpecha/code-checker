<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Rules\Php;

	use CzProject\PhpSimpleAst;
	use JP\CodeChecker\CommitMessage;
	use JP\CodeChecker\File;
	use JP\CodeChecker\Rules\PhpReflectionRule;


	class PhpNullableParameterFixerRule implements PhpReflectionRule
	{
		public function getCommitMessage(): ?CommitMessage
		{
			return new CommitMessage(
				subject: 'Fixed nullable parameters'
			);
		}


		public function processPhpReflection(File $file, PhpSimpleAst\Reflection\Reflection $phpReflection): void
		{
			foreach ($phpReflection->getClasses() as $class) {
				$className = $class->getName();
				$pos = strrpos($className, '\\');
				$classShortName = $pos !== FALSE ? substr($className, $pos + 1) : $className;

				foreach ($class->getMethods() as $method) {
					foreach ($method->getParameters() as $parameter) {
						$parameterType = $parameter->getType();

						if ($parameterType === NULL) {
							continue;
						}

						if (!$parameterType->isSingle()) {
							continue;
						}

						if ($parameterType->isNullable()) { // already fixed
							continue;
						}

						$parameterDefaultValue = $parameter->getDefaultValue();

						if ($parameterDefaultValue !== NULL && $parameterDefaultValue->isNull()) {
							$parameterType->setNullable(TRUE);
							$file->reportFix("Fixed nullable parameter \${$parameter->getName()} in method {$classShortName}::{$method->getName()}()");
						}
					}
				}
			}
		}
	}
