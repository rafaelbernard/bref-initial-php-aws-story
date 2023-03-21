import { CfnOutput, RemovalPolicy, Stack, StackProps } from 'aws-cdk-lib';
import { Construct } from 'constructs';
import { Code, Function as LambdaFunction, FunctionUrlAuthType, LayerVersion, Runtime } from 'aws-cdk-lib/aws-lambda';
import { join } from 'path';
import { Bucket } from 'aws-cdk-lib/aws-s3';

export class CdkStack extends Stack {

  // Get Bref layer ARN from https://runtimes.bref.sh/
  public static brefLayerFunctionArn = 'arn:aws:lambda:us-east-1:209497400698:layer:php-82:19';

  constructor(scope: Construct, id: string, props?: StackProps) {
    super(scope, id, props);

    const stackPrefix = 'BrefStory';

    const layer = LayerVersion.fromLayerVersionArn(this, 'php-layer', CdkStack.brefLayerFunctionArn);

    const brefBucket = new Bucket(this, `${stackPrefix}Bucket`, {
      autoDeleteObjects: true,
      removalPolicy: RemovalPolicy.DESTROY,
    });

    const functionName = 'GetFibonacciImage';

    const getLambda = new LambdaFunction(this, `${stackPrefix}${functionName}`, {
      layers: [layer],
      handler: 'get.php',
      runtime: Runtime.PROVIDED_AL2,
      code: Code.fromAsset(join(__dirname, `../assets/get`)),
      functionName,
      environment: {
        BUCKET_NAME: brefBucket.bucketName,
      },
    });

    brefBucket.grantReadWrite(getLambda);

    const fnUrl = getLambda.addFunctionUrl({ authType: FunctionUrlAuthType.NONE });

    new CfnOutput(this, 'TheUrl', {
      // The .url attributes will return the unique Function URL
      value: fnUrl.url,
    });

    new CfnOutput(this, 'Bucket', { value: brefBucket.bucketName });
  }
}
