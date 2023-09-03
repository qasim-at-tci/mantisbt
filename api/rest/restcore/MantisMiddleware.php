<?php
# MantisBT - A PHP based bugtracking system

# MantisBT is free software: you can redistribute it and/or modify
# it under the terms of the GNU General Public License as published by
# the Free Software Foundation, either version 2 of the License, or
# (at your option) any later version.
#
# MantisBT is distributed in the hope that it will be useful,
# but WITHOUT ANY WARRANTY; without even the implied warranty of
# MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
# GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License
# along with MantisBT.  If not, see <http://www.gnu.org/licenses/>.

/**
 * A webservice interface to Mantis Bug Tracker
 *
 * @package   MantisBT
 * @copyright Copyright 2023 MantisBT Team - mantisbt-dev@lists.sourceforge.net
 * @link      https://mantisbt.org
 */

use Psr\Http\Message\ResponseFactoryInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Server\MiddlewareInterface;

/**
 * Abstract base class for MantisBT Middleware.
 */
abstract class MantisMiddleware implements MiddlewareInterface, ResponseFactoryInterface
{
	/** @var ResponseFactoryInterface  */
	protected $responseFactory;

	/**
	 * Middleware constructor.
	 *
	 * Initialization:
	 * $app = \Slim\Factory\AppFactory::create();
	 * $app->addMiddleware( new MyMiddleware( $app->getResponseFactory() ) );
	 *
	 * Example usage (in process() method):
	 * $this->createResponse(403, 'Access denied');
	 *
	 * @param ResponseFactoryInterface $responseFactory
	 */
	public function __construct( ResponseFactoryInterface $responseFactory )
	{
		$this->responseFactory = $responseFactory;
	}

	/**
	 * @inheritDoc
	 */
	public function createResponse( int $code = 200, string $reasonPhrase = '' ): ResponseInterface {
		return $this->responseFactory->createResponse();
	}

}
