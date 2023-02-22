import { CfnOutput, Stack, StackProps } from 'aws-cdk-lib';
import { Construct } from 'constructs';
import { Code, Function as LambdaFunction, FunctionUrlAuthType, LayerVersion, Runtime } from 'aws-cdk-lib/aws-lambda';
import { join } from 'path';
import { PhpFunction } from '@bref.sh/constructs';

export class CdkStack extends Stack {

  // Get Bref layer ARN from https://runtimes.bref.sh/
  public static brefLayerFunctionArn = 'arn:aws:lambda:us-east-1:209497400698:layer:php-82:16';

  constructor(scope: Construct, id: string, props?: StackProps) {
    super(scope, id, props);

    const layer = LayerVersion.fromLayerVersionArn(this, 'php-layer', CdkStack.brefLayerFunctionArn);

    const getLambda = new LambdaFunction(this, 'get', {
      layers: [layer],
      handler: 'get.php',
      runtime: Runtime.PROVIDED_AL2,
      code: Code.fromAsset(join(__dirname, `../assets/get`)),
      functionName: 'part1-get',
    });

    const brefLambda = new PhpFunction(this, 'get-bref', {
      handler: '../assets/get/get.php',
      // code: Code.fromAsset(join(__dirname, `../assets/get`)),
      // functionName: 'part1-get-bref',
      // phpVersion: "8.2"
    });

    const fnUrl = getLambda.addFunctionUrl({authType: FunctionUrlAuthType.NONE});
    const fnUrlBref = brefLambda.addFunctionUrl({authType: FunctionUrlAuthType.NONE});

    new CfnOutput(this, 'TheUrl', {
      // The .url attributes will return the unique Function URL
      value: fnUrl.url,
    });
    new CfnOutput(this, 'TheUrlBref', {
      // The .url attributes will return the unique Function URL
      value: fnUrlBref.url,
    });
  }
}
