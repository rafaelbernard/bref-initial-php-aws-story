import { Stack, StackProps } from 'aws-cdk-lib';
import { Construct } from 'constructs';
import { Code, Function as LambdaFunction, Runtime } from 'aws-cdk-lib/aws-lambda';
import { LayerVersion } from 'aws-cdk-lib/aws-lambda';
import {join} from 'path';

export class CdkStack extends Stack {
  constructor(scope: Construct, id: string, props?: StackProps) {
    super(scope, id, props);
    // // Get Bref layer ARN from https://runtimes.bref.sh/
    // const laravelOnly = new ServerlessLaravel(this, 'serverless-laravel', {
    //   brefLayerVersion: 'arn:aws:lambda:us-east-1:209497400698:layer:php-81-fpm:19',
    //   laravelPath: path.join(__dirname, '../php'),
    // });

    const brefLayerFunctionArn = 'arn:aws:lambda:us-east-1:209497400698:layer:php-81:24';

    const layer = LayerVersion.fromLayerVersionArn(this, 'php-layer', brefLayerFunctionArn);

    const getLambda = new LambdaFunction(this, 'get', {
      layers: [layer],
      handler: 'get.php',
      runtime: Runtime.PROVIDED_AL2,
      code: Code.fromAsset(join(__dirname,  `../assets/get`)),
    });
  }
}
