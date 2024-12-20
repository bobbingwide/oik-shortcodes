<?php // (C) Copyright Bobbing Wide 2013-2016
/**
 * Code originally imported from phpDocumentor
 *
 * PHP Version 5
 *
 * @author    Mike van Riel <mike.vanriel@naenius.com>
 * @copyright 2010-2011 Mike van Riel / Naenius (http://www.naenius.com)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      http://phpdoc.org
 * 
 * original source: c:\apache\htdocs\phpDocumentor2\vendor\phpdocumentor\reflection-docblock\src\phpDocumentor\Reflection\DocBlock.php
 * latest source, for parts of the code: c:\github\phpDocumentor\ReflectionDocBlock\src\DocBlockFactory.php
 * 
 * Some new versions of the code imported from [github phpDocumentor ReflectionDocBlock] ( on 2015/09/18 ) 
 * since I was getting a WSOD. But it turned out it was a stack size problem, with my stack being only 1MB. 
 * So I upped it to 8MB and the problem went away.
 * 
 * {@link http://stackoverflow.com/questions/5058845/how-do-i-increase-the-stack-size-for-apache-running-under-windows-7}
 *
 */

//namespace phpDocumentor\Reflection;

/**
 * Parses the DocBlock for any structure.
 *
 * @author   Mike van Riel <mike.vanriel@naenius.com>
 * @license  http://www.opensource.org/licenses/mit-license.php MIT
 * @link     http://phpdoc.org
 */
class DocBlock //implements \Reflector
{
    /** @var string The opening line for this docblock. */
    protected $short_description = '';

    /**
     * @var \phpDocumentor\Reflection\DocBlock\LongDescription The actual description
     *     for this docblock.
     */
    protected $long_description = null;

    /**
     * @var \phpDocumentor\Reflection\DocBlock\Tags[] An array containing all the tags
     *     in this docblock; except inline.
     */
    protected $tags = array();

    /** @var string the current namespace */
    protected $namespace = '\\';

    /** @var string[] List of namespace aliases => Fully Qualified Namespace */
    protected $namespace_aliases = array();

    /**
     * Parses the given docblock and populates the member fields.
     *
     * The constructor may also receive namespace information such as the
     * current namespace and aliases. This information is used in the
     * {@link expandType()} method to transform a relative Type into a FQCN.
     *
     * For example the param and return tags use this to expand their type
     * information.
     *
     * @param \Reflector|string $docblock A docblock comment (including asterisks)
     *     or reflector supporting the getDocComment method.
     * @param string $namespace The namespace where this DocBlock resides in;
     *    defaults to `\`.
     * @param string[] $namespace_aliases a list of namespace aliases as
     *     provided by the `use` keyword; the key of the array is the alias name
     *     or last part of the alias array if no alias name is provided.
     *
     * @throws \InvalidArgumentException if the given argument does not have the
     *     getDocComment method.
     */
    public function __construct(
        $docblock, $namespace = '\\', $namespace_aliases = array()
    ) {
				//bw_trace2();
        if (is_object($docblock)) {
            if (!method_exists($docblock, 'getDocComment')) {
                throw new \InvalidArgumentException(
                    'Invalid object passed; the given reflector must support '
                    . 'the getDocComment method'
                );
            }

            $docblock = $docblock->getDocComment();
						//bw_trace2( $docblock );
						
        }
				
        $docblock = $this->stripDocComment($docblock);
				
				//bw_trace2( $docblock, "After stripDocComment", false, BW_TRACE_VERBOSE );

        $matches = $this->splitDocBlock( $docblock );
				
				//bw_trace2( $matches, "After splitDocBlock", false, BW_TRACE_VERBOSE ); 
				
				// We now get 4 parts
				list( $template_marker, $short, $long, $tags) =	 $matches;
        $this->short_description = $short;
        $this->long_description = $long; // new DocBlock\LongDescription($long);
        $this->parseTags($tags);
				

        $this->namespace = $namespace;
        $this->namespace_aliases = $namespace_aliases;
        //bw_trace2( $this, "wofdb" );
        //bw_backtrace();
    }
		
		/**
     * Strips the asterisks from the DocBlock comment.
     *
     * @param string $comment String containing the comment text.
     *
     * @return string
     */
    private function stripDocComment($comment)
    {
		if ( !$comment) {
			return $comment;
		}
		$comment=trim( preg_replace( '#[ \t]*(?:\/\*\*|\*\/|\*)?[ \t]{0,1}(.*)?#u', '$1', $comment ) );

        // reg ex above is not able to remove */ from a single line docblock
        if (substr($comment, -2) == '*/') {
            $comment = trim(substr($comment, 0, -2));
        }
        return str_replace(array("\r\n", "\r"), "\n", $comment);
    }

    /**
     * Splits the DocBlock into a template marker, summary, description and block of tags.
     *
     * @param string $comment Comment to split into the sub-parts.
     *
     * @author Richard van Velzen (@_richardJ) Special thanks to Richard for the regex responsible for the split.
     * @author Mike van Riel <me@mikevanriel.com> for extending the regex with template marker support.
     *
     * @return string[] containing the template marker (if any), summary, description and a string containing the tags.
     */
    private function splitDocBlock($comment)
    {
        // Performance improvement cheat: if the first character is an @ then only tags are in this DocBlock. This
        // method does not split tags so we return this verbatim as the fourth result (tags). This saves us the
        // performance impact of running a regular expression
        if (strpos($comment, '@') === 0) {
            return array('', '', '', $comment);
        }
				//bw_trace2( "after strpos" );
				//bw_backtrace( BW_TRACE_DEBUG );

        // clears all extra horizontal whitespace from the line endings to prevent parsing issues
        $comment = preg_replace('/\h*$/Sum', '', $comment);
				//bw_trace2( $comment, "comment", false, BW_TRACE_DEBUG );

        /*
         * Splits the docblock into a template marker, summary, description and tags section.
         *
         * - The template marker is empty, #@+ or #@- if the DocBlock starts with either of those (a newline may
         *   occur after it and will be stripped).
         * - The short description is started from the first character until a dot is encountered followed by a
         *   newline OR two consecutive newlines (horizontal whitespace is taken into account to consider spacing
         *   errors). This is optional.
         * - The long description, any character until a new line is encountered followed by an @ and word
         *   characters (a tag). This is optional.
         * - Tags; the remaining characters
         *
         * Big thanks to RichardJ for contributing this Regular Expression
         */
        preg_match(
            '/
            \A
            # 1. Extract the template marker
            (?:(\#\@\+|\#\@\-)\n?)?

            # 2. Extract the summary
            (?:
              (?! @\pL ) # The summary may not start with an @
              (
                [^\n.]+
                (?:
                  (?! \. \n | \n{2} )     # End summary upon a dot followed by newline or two newlines
                  [\n.] (?! [ \t]* @\pL ) # End summary when an @ is found as first character on a new line
                  [^\n.]+                 # Include anything else
                )*
                \.?
              )?
            )

            # 3. Extract the description
            (?:
              \s*        # Some form of whitespace _must_ precede a description because a summary must be there
              (?! @\pL ) # The description may not start with an @
              (
                [^\n]+
                (?: \n+
                  (?! [ \t]* @\pL ) # End description when an @ is found as first character on a new line
                  [^\n]+            # Include anything else
                )*
              )
            )?

            # 4. Extract the tags (anything that follows)
            (\s+ [\s\S]*)? # everything that follows
            /ux',
            $comment,
            $matches
        );
        array_shift($matches);

        while (count($matches) < 4) {
            $matches[] = '';
        }

        return $matches;
    }

    /**
     * Creates the tag objects.
     *
     * @param string $tags Tag block to parse.
     *
     * @return void
     */
    protected function parseTags($tags)
    {
      //bw_trace2( $tags, "tags" );
        $result = array();
        foreach (explode("\n", trim($tags)) as $tag_line) {
            if (trim($tag_line) === '') {
                continue;
            }

            $tag_line = ltrim($tag_line);

            if (isset($tag_line[0]) && ($tag_line[0] === '@')) {
                $result[] = $tag_line;
            } else {
                if (count($result) == 0) {
                    throw new \LogicException(
                        'A tag block started with text instead of an actual tag,'
                        . ' this makes the tag block invalid: ' . $tags
                    );
                }

                $result[count($result) - 1] .= PHP_EOL . $tag_line;
            }
        }

        // create proper Tag objects
        //foreach ($result as $key => $tag_line) {
            //$tag = DocBlock\Tag::createInstance($tag_line);
            //$tag->setDocBlock($this);
            //$result[$key] = $tag;
        //}

        $this->tags = $result;
        
      //bw_trace3( $result, "result" );
    }

    /**
     * Returns the opening line or also known as short description.
     *
     * @return string
     */
    public function getShortDescription()
    {
        return $this->short_description;
    }

    /**
     * Returns the full description or also known as long description.
     *
     * @return \phpDocumentor\Reflection\DocBlock\LongDescription
     */
    public function getLongDescription()
    {
        return $this->long_description;
    }

    /**
     * Returns the tags for this DocBlock.
     *
     * @return \phpDocumentor\Reflection\DocBlock\Tag[]
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Returns an array of tags matching the given name; if no tags are found
     * an empty array is returned.
     *
     * @param string $name String to search by.
     *
     * @return \phpDocumentor\Reflection\DocBlock_Tag[]
     */
    public function getTagsByName($name)
    {
        $result = array();

        /** @var \phpDocumentor\Reflection\DocBlock\Tag $tag */
        foreach ($this->getTags() as $key => $tag) {
            //if ($tag->getName() != $name) {
            //    continue;
            // }
            //echo $name . $tag . PHP_EOL;
            $pos = strpos( $tag, "@$name " );
            //echo "strpos:$pos!" . PHP_EOL;
            //print_r( $pos );
            if ( $pos !== false ) {
              $result[] = substr( $tag, strlen( "@$name " ));
            }
        }

        return $result;
    }

    /**
     * Checks if a tag of a certain type is present in this DocBlock.
     *
     * @param string $name Tag name to check for.
     *
     * @return bool
     */
    public function hasTag($name)
    {
        /** @var \phpDocumentor\Reflection\DocBlock\Tag $tag */
        foreach ($this->getTags() as $tag) {
            if ($tag->getName() == $name) {
                return true;
            }
        }

        return false;
    }

    /**
     * Tries to expand a type to it's full namespaced equivalent (FQCN).
     *
     * This method will take the given type and examine the current namespace
     * and namespace aliases to see whether it should expand it into a FQCN
     * as defined by the rules in PHP.
     *
     * @param string   $type            Type to expand into full namespaced
     *     equivalent.
     * @param string[] $ignore_keywords Whether to ignore given keywords, when
     *     null it will use the default keywords: 'string', 'int', 'integer',
     *     'bool', 'boolean', 'float', 'double', 'object', 'mixed', 'array',
     *     'resource', 'void', 'null', 'callback', 'false', 'true'.
     *     Default value for this parameter is null.
     *
     * @return string
     */
    public function expandType($type, $ignore_keywords = null)
    {
        if ($type === null) {
            return null;
        }

        if ($ignore_keywords === null) {
            $ignore_keywords = array(
                'string', 'int', 'integer', 'bool', 'boolean', 'float', 'double',
                'object', 'mixed', 'array', 'resource', 'void', 'null',
                'callback', 'false', 'true'
            );
        }

        $namespace = '';
        if ($this->namespace != 'default' && $this->namespace != 'global') {
            $namespace = rtrim($this->namespace, '\\') . '\\';
        }

        $type = explode('|', $type);
        foreach ($type as &$item) {
            $item = trim($item);

            // add support for array notation
            $is_array = false;
            if (substr($item, -2) == '[]') {
                $item = substr($item, 0, -2);
                $is_array = true;
            }

            if ((substr($item, 0, 1) != '\\')
                && (!in_array(strtolower($item), $ignore_keywords))
            ) {
                $type_parts = explode('\\', $item);

                // if the first segment is an alias; replace with full name
                if (isset($this->namespace_aliases[$type_parts[0]])) {
                    $type_parts[0] = $this->namespace_aliases[$type_parts[0]];
                    $item = implode('\\', $type_parts);
                } else {
                    // otherwise prepend the current namespace
                    $item = $namespace . $item;
                }
            }

            // full paths always start with a slash
            if (isset($item[0]) && ($item[0] !== '\\')
                && (!in_array(strtolower($item), $ignore_keywords))
            ) {
                $item = '\\' . $item;
            }

            // re-add the array notation markers
            if ($is_array) {
                $item .= '[]';
            }
        }

        return implode('|', $type);
    }

    /**
     * Builds a string representation of this object.
     *
     * @todo determine the exact format as used by PHP Reflection and
     *     implement it.
     *
     * @return string
     */
    static public function export()
    {
        throw new \Exception('Not yet implemented');
    }

    /**
     * Returns the exported information (we should use the export static method
     * BUT this throws an exception at this point).
     *
     * @return string
     */
    public function __toString()
    {
        return 'Not yet implemented';
    }

}
