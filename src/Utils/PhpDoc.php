<?php

	declare(strict_types=1);

	namespace JP\CodeChecker\Utils;

	use PHPStan\PhpDocParser;


	class PhpDoc
	{
		private function __construct()
		{
		}


		public static function removeTag(
			PhpDocParser\Ast\PhpDoc\PhpDocNode $phpDoc,
			PhpDocParser\Ast\PhpDoc\PhpDocTagNode $phpDocTag
		): void
		{
			foreach ($phpDoc->children as $key => $child) {
				if ($child === $phpDocTag) {
					unset($phpDoc->children[$key]);
				}
			}
		}


		public static function hasTag(
			PhpDocParser\Ast\PhpDoc\PhpDocNode $phpDoc,
			string $name
		): bool
		{
			return count($phpDoc->getTagsByName($name)) > 0;
		}


		public static function addReturnTag(
			PhpDocParser\Ast\PhpDoc\PhpDocNode $phpDoc,
			string $returnType
		): void
		{
			$phpDoc->children[] = new PhpDocParser\Ast\PhpDoc\PhpDocTagNode(
				'@return',
				new PhpDocParser\Ast\PhpDoc\ReturnTagValueNode(
					new PhpDocParser\Ast\Type\IdentifierTypeNode($returnType),
					''
				)
			);
		}
	}
